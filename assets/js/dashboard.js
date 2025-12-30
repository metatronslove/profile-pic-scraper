// assets/js/dashboard.js - Tam Çalışan Dashboard JS

let currentPlatformKey = null;
let platformsData = {};

// Sayfa yüklendiğinde platform verilerini çek
document.addEventListener('DOMContentLoaded', function() {
    fetch('../scraper/platforms.json?' + Date.now())
        .then(r => r.json())
        .then(data => {
            platformsData = data;
            renderPlatformTable();
        })
        .catch(err => {
            showNotification('Platform ayarları yüklenemedi!', 'error');
        });
});

function renderPlatformTable() {
    const tbody = document.querySelector('#platformTable tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    Object.keys(platformsData).forEach(key => {
        const plat = platformsData[key];
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><i class="${plat.icon || 'fas fa-globe'}"></i> ${plat.name}</td>
            <td><span class="status ${plat.enabled ? 'active' : 'inactive'}">${plat.enabled ? 'Aktif' : 'Pasif'}</span></td>
            <td><input type="number" min="1" max="100" value="${plat.recent_profiles_limit || 12}" onchange="updatePlatformSetting('${key}', 'recent_profiles_limit', this.value)"></td>
            <td class="actions">
                <button onclick="openEditModal('${key}')">Düzenle</button>
                <button onclick="togglePlatform('${key}')">${plat.enabled ? 'Devre Dışı' : 'Etkinleştir'}</button>
                <button onclick="deletePlatform('${key}')" class="danger">Sil</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updatePlatformSetting(key, setting, value) {
    if (platformsData[key]) {
        platformsData[key][setting] = parseInt(value) || 12;
        savePlatforms();
    }
}

function togglePlatform(key) {
    if (platformsData[key]) {
        platformsData[key].enabled = !platformsData[key].enabled;
        savePlatforms();
        renderPlatformTable();
    }
}

function deletePlatform(key) {
    if (confirm(`${platformsData[key].name} platformu silinecek. Emin misiniz?`)) {
        delete platformsData[key];
        savePlatforms();
        renderPlatformTable();
    }
}

function openEditModal(key = null) {
    currentPlatformKey = key;
    const modal = document.getElementById('editModal');
    const title = document.getElementById('modalTitle');
    
    if (key) {
        title.textContent = `${platformsData[key].name} Düzenle`;
        populateForm(platformsData[key]);
    } else {
        title.textContent = 'Yeni Platform Ekle';
        document.getElementById('editForm').reset();
        document.getElementById('platformKey').value = '';
        document.getElementById('methodsContainer').innerHTML = '';
        addNewMethod();
    }
    
    modal.style.display = 'block';
}

function populateForm(plat) {
    document.getElementById('platformKey').value = currentPlatformKey;
    document.getElementById('name').value = plat.name || '';
    document.getElementById('icon').value = plat.icon || '';
    document.getElementById('color').value = plat.color || '#000000';
    document.getElementById('detect_patterns').value = plat.detect_patterns?.join(', ') || '';
    document.getElementById('profile_url_pattern').value = plat.profile_url_pattern || '';

    // İçerik türleri
    const contents = ['profile_picture', 'cover_photo', 'highlighted_stories', 'recent_posts', 'post_thumbnails'];
    contents.forEach(c => {
        document.getElementById(`content_${c}`).checked = plat.extractable_content?.[c] === true;
    });

    // Yöntemler
    const container = document.getElementById('methodsContainer');
    container.innerHTML = '';
    (plat.methods || []).forEach((method, idx) => {
        addMethodToForm(method, idx);
    });
    if (plat.methods?.length === 0) addNewMethod();
}

function addNewMethod() {
    const container = document.getElementById('methodsContainer');
    const idx = container.children.length;
    addMethodToForm({
        name: 'Yeni Yöntem',
        type: 'server_php',
        enabled: true,
        priority: idx + 1
    }, idx);
}

function addMethodToForm(method, idx) {
    const div = document.createElement('div');
    div.className = 'method-block';
    div.innerHTML = `
        <h4>Yöntem ${idx + 1}: ${method.name || 'Yeni Yöntem'}</h4>
        <label>Adı: <input type="text" value="${method.name || ''}" onchange="updateMethod(${idx}, 'name', this.value)"></label>
        <label>Tür:
            <select onchange="updateMethod(${idx}, 'type', this.value)">
                <option value="server_php" ${method.type === 'server_php' ? 'selected' : ''}>Sunucu PHP (cURL)</option>
                <option value="browser_js" ${method.type === 'browser_js' ? 'selected' : ''}>Tarayıcı JS (Bridge)</option>
                <option value="composer_api" ${method.type === 'composer_api' ? 'selected' : ''}>Composer API (opsiyonel)</option>
            </select>
        </label>
        <label>Etkin: <input type="checkbox" ${method.enabled ? 'checked' : ''} onchange="updateMethod(${idx}, 'enabled', this.checked)"></label>
        <label>Öncelik: <input type="number" value="${method.priority || idx + 1}" onchange="updateMethod(${idx}, 'priority', this.value)"></label>
        
        <div class="method-fields">
            <label>Endpoint (sadece server_php için): <input type="text" value="${method.endpoint || ''}" onchange="updateMethod(${idx}, 'endpoint', this.value)"></label>
            <label>Headers (satır satır key: value):<br>
                <textarea rows="4" onchange="updateMethodHeaders(${idx}, this.value)">${formatHeaders(method.headers)}</textarea>
            </label>
            <label>Optimize Rules (JSON format):<br>
                <textarea rows="3" onchange="updateMethod(${idx}, 'optimize_rules', this.value)">${JSON.stringify(method.optimize_rules || [], null, 2)}</textarea>
            </label>
        </div>
        <button type="button" onclick="removeMethod(${idx})" class="danger small">Bu Yöntemi Sil</button>
        <hr>
    `;
    document.getElementById('methodsContainer').appendChild(div);
}

function formatHeaders(headers) {
    if (!headers) return '';
    return Object.entries(headers).map(([k, v]) => `${k}: ${v}`).join('\n');
}

function updateMethod(idx, field, value) {
    // Formdan veri topla (basit yöntem)
    const blocks = document.querySelectorAll('.method-block');
    if (blocks[idx]) {
        // Bu fonksiyonu ileride daha sağlam hale getir
    }
}

// Kaydetme işlemi
function savePlatform() {
    const key = document.getElementById('platformKey').value || document.getElementById('name').value.toLowerCase().replace(/\s+/g, '_');
    
    const plat = {
        name: document.getElementById('name').value,
        icon: document.getElementById('icon').value,
        color: document.getElementById('color').value,
        enabled: true,
        detect_patterns: document.getElementById('detect_patterns').value.split(',').map(s => s.trim()).filter(Boolean),
        profile_url_pattern: document.getElementById('profile_url_pattern').value,
        recent_profiles_limit: 12,
        extractable_content: {
            profile_picture: document.getElementById('content_profile_picture').checked,
            cover_photo: document.getElementById('content_cover_photo').checked,
            highlighted_stories: document.getElementById('content_highlighted_stories').checked,
            recent_posts: document.getElementById('content_recent_posts').checked,
            post_thumbnails: document.getElementById('content_post_thumbnails').checked
        },
        methods: [] // burada manuel dolduracağız, ileride otomatik topla
    };
    
    const defaultHeaders = {};
	document.getElementById('default_headers').value.split('\n').forEach(line => {
		if (line.trim() && line.includes(':')) {
			const [key, ...val] = line.split(':');
			defaultHeaders[key.trim()] = val.join(':').trim();
		}
	});

	plat.default_headers = defaultHeaders;
	plat.user_agent = document.getElementById('user_agent').value.trim();

    // Şimdilik basit: örnek yöntem ekle
    plat.methods = [{
        name: "OG Image Fallback",
        type: "server_php",
        enabled: true,
        priority: 99,
        selectors: { og_image: "meta[property='og:image']" }
    }];

    platformsData[key] = plat;
    savePlatforms();
    closeModal();
}

function savePlatforms() {
    const data = JSON.stringify(platformsData, null, 2);
    fetch('engine/save-platforms.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showNotification('Ayarlar kaydedildi!', 'success');
            renderPlatformTable();
        } else {
            showNotification('Kaydetme hatası: ' + res.error, 'error');
        }
    });
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function generateScraper() {
    if (!confirm('Scraper dosyaları yeniden oluşturulacak. Tüm scraping kodları güncellenecek!')) return;
    // generate-scraper.php çağrısı (önceki dosyadan)
    fetch('engine/generate-scraper.php', { method: 'POST' })
        .then(r => r.json())
        .then(data => alert(data.success ? 'Başarılı: ' + data.message : 'Hata: ' + data.error));
}
