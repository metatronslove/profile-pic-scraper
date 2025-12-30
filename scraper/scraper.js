// scraper/scraper.js - OTOMATİK OLUSTURULDU - DÜZENLEMEYİN
// Tarayıcı köprüsü için client-side scraping yöntemleri

window.ScraperBridge = {
    config: null,

    init() {
        fetch('../scraper/platforms.json?' + Date.now())
            .then(r => r.json())
            .then(data => {
                this.config = data;
                console.log('ScraperBridge yüklendi - Platform sayısı:', Object.keys(data).length);
            });
    },

    // Ana fonksiyon: URL verildiğinde uygun platform yöntemini çalıştır
    async fetchProfileData(url) {
        const platformInfo = this.detectPlatform(url);
        if (!platformInfo) return { success: false, error: 'Platform desteklenmiyor' };

        const { platform, identifier } = platformInfo;
        const platConfig = this.config[platform];
        if (!platConfig || !platConfig.enabled) return { success: false, error: 'Platform devre dışı' };

        const methods = (platConfig.methods || []).filter(m => m.enabled && m.type === 'browser_js');
        methods.sort((a, b) => (a.priority || 99) - (b.priority || 99));

        for (const method of methods) {
            try {
                const result = await this.executeBrowserMethod(method, identifier, url);
                if (result && Object.keys(result.contents || {}).length > 0) {
                    return { success: true, platform: platConfig.name, ...result };
                }
            } catch (e) {
                console.warn('Browser yöntemi başarısız:', method.name, e);
            }
        }

        return { success: false, error: 'Hiçbir tarayıcı yöntemi çalışmadı' };
    },

    detectPlatform(url) {
        for (const [key, plat] of Object.entries(this.config)) {
            if (!plat.enabled) continue;
            for (const pattern of plat.detect_patterns || []) {
                if (url.includes(pattern)) {
                    const match = url.match(new RegExp(plat.profile_url_pattern.replace('{identifier}', '([\\w.\\-]+)')));
                    if (match) {
                        return { platform: key, identifier: match[1] };
                    }
                }
            }
        }
        return null;
    },

    async executeBrowserMethod(method, identifier, originalUrl) {
        const contents = {};

        // Yönteme göre farklı stratejiler
        if (method.strategy === 'iframe_load') {
            return await this.iframeStrategy(originalUrl, method);
        }

        if (method.strategy === 'fetch_api') {
            // Örneğin Instagram GraphQL
            const endpoint = method.endpoint?.replace('{username}', identifier);
            if (endpoint) {
                const headers = new Headers();
                Object.entries(method.headers || {}).forEach(([k, v]) => {
                    headers.append(k, v.replace('{username}', identifier));
                });

                try {
                    const res = await fetch(endpoint, { headers });
                    if (res.ok) {
                        const json = await res.json();
                        return this.extractFromJson(json, method, platConfig);
                    }
                } catch (e) {}
            }
        }

        // Varsayılan: og:image ve selector tabanlı
        return await this.defaultDomStrategy(originalUrl, method);
    },

    async iframeStrategy(url, method) {
        return new Promise((resolve) => {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;

            iframe.onload = () => {
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    const result = this.parseDom(doc, method);
                    document.body.removeChild(iframe);
                    resolve(result);
                } catch (e) {
                    resolve({ success: false });
                }
            };

            iframe.onerror = () => {
                document.body.removeChild(iframe);
                resolve({ success: false });
            };

            setTimeout(() => {
                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                    resolve({ success: false, error: 'Timeout' });
                }
            }, 15000);

            document.body.appendChild(iframe);
        });
    },

    async defaultDomStrategy(url, method) {
        try {
            const res = await fetch(url);
            if (!res.ok) return null;
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            return this.parseDom(doc, method);
        } catch (e) {
            return null;
        }
    },

    parseDom(doc, method) {
        const contents = {};
        const platConfig = this.config[this.detectPlatform(doc.URL)?.platform];

        // og:image
        const ogImage = doc.querySelector('meta[property="og:image"]')?.content;
        if (ogImage) {
            let optimized = ogImage;
            (method.optimize_rules || []).forEach(rule => {
                optimized = optimized.replace(rule.find, rule.replace);
            });
            if (platConfig.extractable_content.profile_picture) {
                contents.profile_picture = optimized;
            }
        }

        // Diğer selector'lar
        Object.entries(method.selectors || {}).forEach(([key, selector]) => {
            const el = doc.querySelector(selector);
            if (el) {
                let value = el.content || el.src || el.href || el.textContent.trim();
                if (key.includes('image') && method.optimize_rules) {
                    method.optimize_rules.forEach(rule => {
                        value = value.replace(rule.find, rule.replace);
                    });
                }
                contents[key] = value;
            }
        });

        return Object.keys(contents).length > 0 ? { contents } : null;
    }
};

// Sayfa yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', () => {
    window.ScraperBridge.init();
});
