<?php
session_start();

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['device_id']) && !empty($_SESSION['device_id']);

// Cek juga dari data.json untuk memastikan konsistensi
if ($isLoggedIn) {
    $dataFile = __DIR__ . '/data.json';
    if (file_exists($dataFile)) {
        $data = json_decode(file_get_contents($dataFile), true);
        if (!isset($data['logged_in']) || $data['logged_in'] !== true) {
            $isLoggedIn = false;
            session_destroy();
        }
    }
}

// Data user
$userEmail = $isLoggedIn ? 'device@neoda.local' : 'guest@neoda.local';
$deviceId = $isLoggedIn ? ($_SESSION['device_id'] ?? 'N/A') : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Water Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="header">
    <h2>
        <img src="pict/logo.png" alt="Neoda logo" class="logo">
        
        Water Control System
        <i class="fas fa-droplet"></i>
    </h2>
</div>

<!-- Loading overlay -->
<div id="loaderOverlay" role="status" aria-live="polite">
    <div class="loader-box">
        <div class="spinner" aria-hidden="true"></div>
        <div class="loader-text">Memuat Web...</div>
    </div>
</div>

<!-- Taskbar -->
<div class="taskbar" role="navigation" aria-label="Taskbar">
    <button class="task-item" onclick="openModal('settingsModal')" aria-label="Settings">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
    </button>
    <button class="task-item" onclick="openModal('contactModal')" aria-label="Contact">
        <i class="fas fa-address-book"></i>
        <span>Contact</span>
    </button>
    <button class="task-item" onclick="openModal('aboutModal')" aria-label="About">
        <i class="fas fa-info-circle"></i>
        <span>About</span>
    </button>
    <button class="task-item" id="authBtn" onclick="handleAuthClick()" aria-label="Login">
        <i class="fas fa-user"></i>
        <span id="authBtnText">Login</span>
    </button>
</div>

<!-- Toast container -->
<div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- Modals -->
<div id="settingsModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content">
        <div class="close" onclick="closeModal('settingsModal')">&times;</div>
        <h3>Settings</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div id="userInfo" style="padding:10px;background:#f0f0f0;border-radius:6px;display:none;">
                <strong id="currentUserEmail"></strong><br>
                <small id="deviceInfo" style="color:#666;margin-top:5px;display:block;"></small>
            </div>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="notifToggle"> <span>Aktifkan Notifikasi</span>
            </label>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button id="logoutBtn" onclick="handleLogout()" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;display:none;cursor:pointer;">Log out</button>
                <button onclick="closeModal('settingsModal')" style="padding:8px 12px;border-radius:6px;border:none;background:var(--primary);color:#fff;cursor:pointer;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div id="contactModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content">
        <div class="close" onclick="closeModal('contactModal')">&times;</div>
        <h3>Contact Person</h3>
        <p>Nama: Admin Sistem<br>Email: Neoda122@gmail.com<br>Telp: +62 812 3456 7890</p>
    </div>
</div>

<div id="aboutModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content">
        <div class="close" onclick="closeModal('aboutModal')">&times;</div>
        <h3>About Us</h3>
        <p>Water Control System NEODA — Sistem monitoring air sederhana untuk demo.</p>
    </div>
</div>

<div id="loginModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content" style="width:90%;max-width:400px;">
        <div class="close" onclick="closeModal('loginModal')">&times;</div>
        <h3>Authentikasi</h3>
        
        <!-- Tab Navigation -->
        <div style="display:flex;gap:10px;margin-bottom:15px;border-bottom:2px solid #ddd;display:none;">
            <button id="loginTab" class="auth-tab active" onclick="switchAuthTab('login')" style="flex:1;padding:10px;border:none;background:none;cursor:pointer;border-bottom:3px solid var(--primary);color:var(--primary);font-weight:bold;">Login</button>
            <button id="registerTab" class="auth-tab" onclick="switchAuthTab('register')" style="flex:1;padding:10px;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#999;font-weight:bold;">Daftar</button>
        </div>

        <!-- Login Form -->
        <form id="loginForm" style="display:block;">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <input id="loginDeviceId" type="text" placeholder="Device ID (contoh: NEO-001)" required style="padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
                <small style="color:#666;">Masukkan ID device yang tersimpan pada perangkat Anda</small>
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">
                    <button type="button" onclick="closeModal('loginModal')" style="padding:10px 15px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer;">Batal</button>
                    <button type="submit" style="padding:10px 15px;border-radius:6px;border:none;background:var(--primary);color:#fff;cursor:pointer;font-weight:bold;">Masuk</button>
                </div>
            </div>
        </form>

        <!-- Register Form -->
        <form id="registerForm" style="display:none;">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <p style="color:#666;font-size:14px;">Fitur pendaftaran telah dinonaktifkan. Gunakan Device ID yang tersimpan pada perangkat Anda.</p>
            </div>
        </form>
    </div>
</div>

<div class="dashboard">
    <div class="card tank-wrapper">
        <h3>Level Air</h3>
        <div class="tank">
            <div id="waterLevel" class="water" style="height: 0%;"></div>
            <div id="overflow"></div>
        </div>
        <div class="value"><span id="levelVal">0</span>%</div>
    </div>

    <div class="card">
        <i class="fas fa-flask"></i>
        <h3>Tingkat pH</h3>
        <div id="phCard" class="value">7.0</div>
        <small id="phStatus">Netral</small>
    </div>

    <div class="card">
        <i class="fas fa-smog"></i>
        <h3>Kekeruhan (Turbidity)</h3>
        <div class="value"><span id="turbValue">0</span> <small>NTU</small></div>
        <small id="turbStatus">Jernih</small>
        <div id="turbLevelIndicator" class="turbidity-level-indicator" style="margin-top: 8px;">
            <div class="level-bar">
                <div id="turbLevelFill" class="level-fill" style="width: 0%;"></div>
            </div>
            <div class="level-labels">
                <span class="label-jernih">Jernih</span>
                <span class="label-netral">Netral</span>
                <span class="label-keruh">Keruh</span>
                <span class="label-sangat-keruh">Sangat Keruh</span>
            </div>
        </div>
    </div>

    <div class="controls">
        <div class="pump-control" id="pumpControl" data-endpoint="function/pump.php">
            <div style="display:flex;flex-direction:column;gap:6px;align-items:center;">
                <div id="pumpState">Pump: <strong id="pumpStateLabel">Off</strong></div>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-pump" id="pumpOn" aria-pressed="false"><i class="fas fa-play"></i>&nbsp;On</button>
                    <button class="btn btn-purifier off" id="pumpOff" aria-pressed="true"><i class="fas fa-stop"></i>&nbsp;Off</button>
                </div>
            </div>
        </div>
        <button class="btn btn-purifier" id="purifierBtn"><i class="fas fa-filter"></i>&nbsp;Purifier</button>
    </div>
</div>

<script>
    // Simulasi Penerimaan Data Real-time (Nanti diganti WebSocket/HTTP Get)

    // Notification helpers
    const _alertTimestamps = { turbidity: 0, ph: 0, level: 0, server: 0 };
    const ALERT_COOLDOWN_MS = 15000; // 15s per alert type to avoid spam

    function showToast(message, level = 'info') {
        const container = document.getElementById('toastContainer');
        if(!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast ' + (level === 'danger' ? 'danger' : (level === 'warn' ? 'warn' : 'info'));
        const dot = document.createElement('div'); dot.className = 'dot';
        if(level === 'danger') dot.style.background = 'var(--danger)';
        else if(level === 'warn') dot.style.background = '#f39c12';
        else dot.style.background = 'var(--primary)';
        const msg = document.createElement('div'); msg.className = 'msg'; msg.innerText = message;
        toast.appendChild(dot);
        toast.appendChild(msg);
        container.appendChild(toast);
        // auto remove
        setTimeout(()=> {
            toast.style.transform = 'translateX(10px)';
            toast.style.opacity = '0';
            setTimeout(()=> toast.remove(), 400);
        }, 5000);
    }

    function notifyOnce(key, message, level) {
        const now = Date.now();
        if(!isNotifEnabled()) return; // respect user setting
        if(!_alertTimestamps[key] || (now - _alertTimestamps[key]) > ALERT_COOLDOWN_MS) {
            _alertTimestamps[key] = now;
            showToast(message, level);
            // also update global status briefly
            const status = document.getElementById('status');
            if(status) {
                const prev = status.innerText;
                status.innerText = 'ALERT: ' + message;
                setTimeout(()=> status.innerText = prev, 5000);
            }
        }
    }

    // Turbidity level classification function
function updateTurbidityLevel(turbidity) {
    const statusElement = document.getElementById('turbStatus');
    const fillElement = document.getElementById('turbLevelFill');
    const indicatorElement = document.getElementById('turbLevelIndicator');

    let levelText = '';
    let levelClass = '';
    let fillPercentage = 0;
    let fillColor = '#2ecc71';

    if (turbidity >= 0 && turbidity <= 5) {
        // ✅ Jernih
        levelText = 'Jernih';
        levelClass = 'level-jernih';
        fillPercentage = 20;
        fillColor = '#2ecc71';
    }
    else if (turbidity > 5 && turbidity <= 25) {
        // 🟡 Netral
        levelText = 'Netral';
        levelClass = 'level-netral';
        fillPercentage = 45;
        fillColor = '#f1c40f';
    }
    else if (turbidity > 25 && turbidity <= 100) {
        // 🟠 Keruh
        levelText = 'Keruh';
        levelClass = 'level-keruh';
        fillPercentage = 75;
        fillColor = '#e67e22';
    }
    else {
        // 🔴 Sangat Keruh
        levelText = 'Sangat Keruh';
        levelClass = 'level-sangat-keruh';
        fillPercentage = 100;
        fillColor = '#e74c3c';
    }

    // Update teks
    statusElement.innerText = levelText;

    // Update bar
    fillElement.style.width = fillPercentage + '%';
    fillElement.style.background = fillColor;

    // Update class indikator
    indicatorElement.classList.remove(
        'level-jernih',
        'level-netral',
        'level-keruh',
        'level-sangat-keruh'
    );
    indicatorElement.classList.add(levelClass);
}


    // Notification settings persisted in localStorage
    function isNotifEnabled() {
        try { return localStorage.getItem('notifEnabled') !== '0'; } catch(e){ return true; }
    }

    function setNotifEnabled(enabled) {
        try { localStorage.setItem('notifEnabled', enabled ? '1' : '0'); } catch(e){}
    }

    // Authentication using localStorage and sessions

    function isLoggedIn() {
        // Check if user has a session (server-side)
        // This is checked on page load via fetch to me endpoint
        return !!localStorage.getItem('device_id');
    }

    function updateAuthUI() {
        const authBtn = document.getElementById('authBtn');
        const authBtnText = document.getElementById('authBtnText');
        const logoutBtn = document.getElementById('logoutBtn');
        const userInfo = document.getElementById('userInfo');
        const currentUserEmail = document.getElementById('currentUserEmail');
        const deviceInfo = document.getElementById('deviceInfo');

        if (isLoggedIn()) {
            const deviceId = localStorage.getItem('device_id');
            authBtnText.innerText = 'Profil';
            logoutBtn.style.display = 'block';
            userInfo.style.display = 'block';
            currentUserEmail.innerText = 'Device ID: ' + deviceId;
            if (deviceInfo) {
                deviceInfo.style.display = 'none';
            }
        } else {
            authBtnText.innerText = 'Login';
            logoutBtn.style.display = 'none';
            userInfo.style.display = 'none';
            if (deviceInfo) deviceInfo.style.display = 'none';
        }
    }

    function disableAllControls(disable) {
        // Disable/enable pump buttons
        const pumpOn = document.getElementById('pumpOn');
        const pumpOff = document.getElementById('pumpOff');
        const purifierBtn = document.getElementById('purifierBtn');
        const dashboard = document.querySelector('.dashboard');

        if (disable) {
            if (pumpOn) pumpOn.disabled = true;
            if (pumpOff) pumpOff.disabled = true;
            if (purifierBtn) purifierBtn.disabled = true;
            if (dashboard) {
                dashboard.style.opacity = '0.5';
                dashboard.style.pointerEvents = 'none';
                dashboard.style.cursor = 'not-allowed';
            }
        } else {
            if (pumpOn) pumpOn.disabled = false;
            if (pumpOff) pumpOff.disabled = false;
            if (purifierBtn) purifierBtn.disabled = false;
            if (dashboard) {
                dashboard.style.opacity = '1';
                dashboard.style.pointerEvents = 'auto';
                dashboard.style.cursor = 'auto';
            }
        }
    }

    function handleAuthClick() {
        if (isLoggedIn()) {
            openModal('settingsModal');
        } else {
            openModal('loginModal');
        }
    }

    async function handleLogout() {
        try {
            const response = await fetch('loginout/device-logout.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            });
            
            const result = await response.json();
            localStorage.removeItem('device_id');
            updateAuthUI();
            disableAllControls(true);
            showToast('✅ Logout berhasil', 'success');
            closeModal('settingsModal');
            
            // Show login modal setelah logout
            setTimeout(() => {
                openModal('loginModal');
                showToast('⚠️ Silakan login kembali untuk melanjutkan', 'warn');
            }, 500);
        } catch(e) {
            console.warn('Logout failed', e);
            localStorage.removeItem('device_id');
            updateAuthUI();
            disableAllControls(true);
        }
    }

    function switchAuthTab(tab) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');

        if (tab === 'login') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            loginTab.style.color = 'var(--primary)';
            loginTab.style.borderBottom = '3px solid var(--primary)';
            registerTab.style.color = '#999';
            registerTab.style.borderBottom = '3px solid transparent';
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            loginTab.style.color = '#999';
            loginTab.style.borderBottom = '3px solid transparent';
            registerTab.style.color = 'var(--primary)';
            registerTab.style.borderBottom = '3px solid var(--primary)';
        }
    }

    async function handleLogin(e) {
        e.preventDefault();
        const device_id = document.getElementById('loginDeviceId').value.trim();

        if (!device_id) {
            showToast('❌ Device ID tidak boleh kosong', 'danger');
            return;
        }

        try {
            const response = await fetch('loginout/device-login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    device_id: device_id
                })
            });

            const result = await response.json();
            if (result.ok) {
                // Simpan device_id ke localStorage
                localStorage.setItem('device_id', device_id);
                updateAuthUI();
                disableAllControls(false); // Enable kontrol setelah login
                closeModal('loginModal');
                showToast('✅ Login berhasil dengan Device: ' + device_id, 'success');
                document.getElementById('loginForm').reset();
                updateData(); // Refresh data
            } else {
                showToast('❌ ' + result.error, 'danger');
            }
        } catch(e) {
            showToast('❌ Login gagal: ' + e.message, 'danger');
            console.warn('Login error', e);
        }
    }

    async function handleRegister(e) {
        e.preventDefault();
        showToast('ℹ️ Fitur pendaftaran telah dinonaktifkan', 'info');
    }

    async function updateData() {
        // Proteksi: jika tidak login, jangan fetch data
        if (!isLoggedIn()) {
            disableAllControls(true);
            return;
        }

        try {
            const res = await fetch('function/data.php', { cache: 'no-store' });
            if (!res.ok) throw new Error("HTTP " + res.status);

            const text = await res.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch (e) {
                console.log("Response bukan JSON:", text);
                throw e;
            }

            if (data.ok === false) {
                document.getElementById('status').innerText = 'Status: Waiting MQTT...';
                return;
            }

            const level = Number(data.level ?? 0);
            if (level <= 100) {
                document.getElementById('waterLevel').style.height = level + '%';
                document.getElementById('overflow').style.display = 'none';
            } else {
                document.getElementById('waterLevel').style.height = '100%';
                document.getElementById('overflow').style.display = 'block';

                notifyOnce(
                    'level',
                    '⚠️ AIR LUBER! Level: ' + level + '%',
                    'danger'
                );
            }
            const ph = Number(data.ph ?? 7.0);
            const turbidity = Number(data.turbidity ?? 0);

            // ===== Pump sync from MQTT =====
            const pump = Number(data.pump ?? -1);

            if (pump === 0 || pump === 1) {
                const KEY = 'neodaPumpState';
                const newState = (pump === 1) ? 'on' : 'off';
                const currentState = localStorage.getItem(KEY);

                if (currentState !== newState) {
                    localStorage.setItem(KEY, newState);

                    const label = document.getElementById('pumpStateLabel');
                    const btnOn = document.getElementById('pumpOn');
                    const btnOff = document.getElementById('pumpOff');

                    if (label && btnOn && btnOff) {
                        label.innerText = (newState === 'on') ? 'On' : 'Off';
                        btnOn.setAttribute('aria-pressed', newState === 'on');
                        btnOff.setAttribute('aria-pressed', newState !== 'on');

                        if (newState === 'on') {
                            btnOn.classList.remove('off');
                            btnOff.classList.remove('off');
                            btnOn.style.boxShadow = '0 6px 18px rgba(0,168,255,0.18)';
                            showToast('Pump NYALA/ON (Server)', 'info');
                        } else {
                            btnOn.classList.add('off');
                            btnOff.classList.add('off');
                            btnOn.style.boxShadow = 'none';
                            showToast('⚠️ Pump MATI/OFF (Server)', 'warn');
                        }
                    }
                }
            }

            // Update UI sensor
            document.getElementById('waterLevel').style.height = level + '%';
            // ==== WARNING AIR LUBER ====
            if (level > 100) {
                notifyOnce(
                    'level',
                    '⚠️ AIR LUBER! Level air melebihi batas (' + level + '%)',
                    'danger'
                );

                // Optional: ubah tampilan biar kelihatan bahaya
                document.getElementById('waterLevel').style.background = '#e74c3c';
            } else {
                document.getElementById('waterLevel').style.background = 'linear-gradient(180deg, #00aaff, #0066cc)';
            }

            document.getElementById('levelVal').innerText = level;

            const phElem = document.getElementById('phCard');
            phElem.innerText = ph.toFixed(1);

            if (ph < 6.5 || ph > 8.5) {
                phElem.classList.add('warning');
                document.getElementById('phStatus').innerText = "Kualitas Buruk!";
                notifyOnce('ph', 'pH di luar rentang aman: ' + ph.toFixed(1), 'danger');
            } else {
                phElem.classList.remove('warning');
                document.getElementById('phStatus').innerText = "Normal";
            }

            document.getElementById('turbValue').innerText = turbidity;
            
            // Update turbidity level classification
            updateTurbidityLevel(turbidity);

            if (turbidity >= 200) {
                notifyOnce('turbidity', 'Kekeruhan tinggi terdeteksi (NTU: ' + turbidity + ')', 'warn');
            }

            const status = document.getElementById('status');
            if (status) status.innerText = 'Status: Connected';

        } catch (e) {
            console.warn('Gagal ambil data sensor', e);
            const status = document.getElementById('status');
            if (status) status.innerText = 'Status: Disconnected';
            notifyOnce('server', 'Gagal ambil data dari server', 'danger');
        }
    }


    function openModal(id) {
        const el = document.getElementById(id);
        if(!el) return;
        el.style.display = 'flex';
        el.setAttribute('aria-hidden','false');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if(!el) return;
        el.style.display = 'none';
        el.setAttribute('aria-hidden','true');
    }

    // Check if user is logged in on page load
    async function checkSession() {
        try {
            const response = await fetch('api/auth.php?action=me', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            });

            const result = await response.json();
            if (result.ok && result.user) {
                localStorage.setItem('user_logged_in', 'true');
                localStorage.setItem('user_email', result.user.email);
                if (result.device) {
                    localStorage.setItem('device_serial_number', result.device.serial_number);
                }
            } else {
                localStorage.removeItem('user_logged_in');
            }
            updateAuthUI();
        } catch(e) {
            console.warn('Session check failed', e);
        }
    }

    // Authentication handler
    document.addEventListener('DOMContentLoaded', function(){
        // Check session first
        checkSession();

        // Setup auth forms
        const loginForm = document.getElementById('loginForm');
        if(loginForm) {
            loginForm.addEventListener('submit', handleLogin);
        }

        const registerForm = document.getElementById('registerForm');
        if(registerForm) {
            registerForm.addEventListener('submit', handleRegister);
        }

        // Initialize UI
        updateAuthUI();

        // PROTEKSI: Jika belum login, show login modal dan disable kontrol
        if (!isLoggedIn()) {
            openModal('loginModal');
            disableAllControls(true);
            showToast('⚠️ Anda harus login terlebih dahulu untuk mengakses aplikasi', 'warn');
        } else {
            disableAllControls(false);
        }

        // Setup notification toggle
        const notifToggle = document.getElementById('notifToggle');
        if(notifToggle) {
            notifToggle.checked = isNotifEnabled();
            notifToggle.addEventListener('change', function(){
                setNotifEnabled(!!this.checked);
                showToast('Notifikasi ' + (this.checked ? 'diaktifkan' : 'dinonaktifkan'), 'info');
            });
        }
    });

    // Loading overlay: hide when window fully loaded, with fade-out
    function hideLoader() {
        const overlay = document.getElementById('loaderOverlay');
        if(!overlay) return;
        overlay.classList.add('hidden');
        // remove from DOM after transition to avoid intercepting focus
        setTimeout(()=> overlay.remove(), 600);
    }

    // If page resources already loaded, hide quickly; otherwise wait for load
    if (document.readyState === 'complete') {
        setTimeout(hideLoader, 300);
    } else {
        window.addEventListener('load', function(){ setTimeout(hideLoader, 300); });
    }

    updateData()
    setInterval(updateData, 1000); // Update setiap 1 detik

    // Pump on/off control for NEODA
(function(){
    const ctrl = document.getElementById('pumpControl');
    const label = document.getElementById('pumpStateLabel');
    const btnOn = document.getElementById('pumpOn');
    const btnOff = document.getElementById('pumpOff');
    if(!ctrl || !label || !btnOn || !btnOff) return;

    const KEY = 'neodaPumpState';
    function readState(){ const s = localStorage.getItem(KEY); return (s === 'on') ? 'on' : 'off'; }
    function saveState(s){ try{ localStorage.setItem(KEY, s); }catch(e){} }

    async function sendCommand(s){
        // Proteksi: check login sebelum mengirim command
        if (!isLoggedIn()) {
            showToast('❌ Anda harus login terlebih dahulu', 'danger');
            openModal('loginModal');
            return;
        }

        const endpoint = ctrl.getAttribute('data-endpoint');
        if(!endpoint) return;
        try{
            const response = await fetch(endpoint, {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ pump: (s === 'on') ? 1 : 0 })
            });
            
            const result = await response.json();
            if (!result.ok) {
                showToast('❌ Error: ' + (result.error || 'Unknown error'), 'danger');
                console.warn('Pump command failed:', result);
            }
        } catch(e){
            showToast('❌ Pump command failed: ' + e.message, 'danger');
            console.warn('Pump command failed', e);
        }
    }

    function updateUI(s){
        label.innerText = (s === 'on') ? 'On' : 'Off';
        btnOn.setAttribute('aria-pressed', s === 'on');
        btnOff.setAttribute('aria-pressed', s !== 'on');
        if(s === 'on') {
            btnOn.classList.remove('off');
            btnOff.classList.remove('off');
            btnOn.style.boxShadow = '0 6px 18px rgba(0,168,255,0.18)';
        } else {
            btnOn.classList.add('off');
            btnOff.classList.add('off');
            btnOn.style.boxShadow = 'none';
        }
    }

    function setState(s){
        saveState(s);
        updateUI(s);
        sendCommand(s);
        showToast('(harap tunggu) Pump' + (s==='on'?'On':'Off'), 'info');
    }

    btnOn.addEventListener('click', ()=> {
        if (!isLoggedIn()) {
            showToast('❌ Anda harus login terlebih dahulu', 'danger');
            openModal('loginModal');
            return;
        }
        setState('on');
    });
    
    btnOff.addEventListener('click', ()=> {
        if (!isLoggedIn()) {
            showToast('❌ Anda harus login terlebih dahulu', 'danger');
            openModal('loginModal');
            return;
        }
        setState('off');
    });

    // initialize
    updateUI(readState());
})();

// Debug biar keliatan errornya
window.onerror = function(msg, url, line, col, err){
    alert("JS Error: " + msg + " line:" + line);
};

</script>

</body>
</html>