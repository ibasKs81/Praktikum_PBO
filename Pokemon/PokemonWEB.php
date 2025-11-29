<?php
session_start();

// --- 1. INTERFACE ---
interface TrainingInterface {
    public function trainEV($statTarget); // Latihan EV
    public function learnTM($jurusBaru);  // Belajar Jurus (TM/TR)
    public function levelUp();            // Naik Level Alami
}

// --- 2. ABSTRACTION (Base Class) ---
abstract class Pokemon implements TrainingInterface {
    protected $nama;
    protected $tipe;
    protected $level;
    
    // Statistik Dasar (Base Stats)
    protected $stats = [
        'hp' => 100,
        'attack' => 50,
        'defense' => 50,
        'speed' => 50
    ];

    // Effort Values (Akumulasi hasil latihan spesifik)
    protected $evs = [
        'hp' => 0, 'attack' => 0, 'defense' => 0, 'speed' => 0
    ];

    protected $moves = []; // Daftar jurus
    protected $riwayat = [];

    // Konfigurasi Multiplier (Akan di-override oleh Child Class)
    // Ini solusi agar program "mudah menyesuaikan gaya pelatihan"
    protected $evMultipliers = [
        'hp' => 1.0, 'attack' => 1.0, 'defense' => 1.0, 'speed' => 1.0
    ];

    public function __construct($nama, $level) {
        $this->nama = $nama;
        $this->level = $level;
        $this->moves[] = "Tackle"; // Jurus default
    }

    // --- LOGIKA UTAMA: EV TRAINING ---
    public function trainEV($statTarget) {
        // Validasi input
        if (!array_key_exists($statTarget, $this->evs)) return;

        // Ambil multiplier berdasarkan Tipe Pokemon (Polimorfisme Data)
        $multiplier = $this->evMultipliers[$statTarget];
        
        // Rumus: Base Gain (4) * Multiplier Tipe
        $gain = 4 * $multiplier;
        
        // Update EV
        $this->evs[$statTarget] += $gain;
        
        // Update Real Stats (Sederhana: Base + EV)
        $this->stats[$statTarget] += $gain;

        $log = "Latihan EV $statTarget. Naik +$gain (Multiplier x$multiplier). Total $statTarget: " . $this->stats[$statTarget];
        $this->addLog($log);
    }

    // --- LOGIKA UTAMA: TM/TR TRAINING ---
    public function learnTM($jurusBaru) {
        // Cek apakah sudah punya jurus itu
        if (in_array($jurusBaru, $this->moves)) {
            $this->addLog("Gagal belajar TM: Sudah menguasai $jurusBaru.");
        } else {
            // Batasi maksimal 4 jurus (hapus yg pertama jika penuh)
            if (count($this->moves) >= 4) {
                $lupa = array_shift($this->moves);
                $this->addLog("Melupakan $lupa demi $jurusBaru!");
            }
            $this->moves[] = $jurusBaru;
            $this->addLog("Sukses belajar TM/TR: $jurusBaru!");
        }
    }

    // --- LOGIKA UTAMA: LEVEL UP ---
    public function levelUp() {
        $this->level++;
        // Semua stat naik sedikit secara alami
        foreach ($this->stats as $key => $val) {
            $this->stats[$key] += 2; 
        }
        $this->addLog("LEVEL UP! Sekarang level $this->level. Semua stats naik +2.");
        
        // Cek jurus alami level tertentu (contoh sederhana)
        if ($this->level == 10) $this->learnTM("Natural Move Lv10");
    }

    protected function addLog($msg) {
        $timestamp = date("H:i:s");
        // Simpan di array paling depan (terbaru)
        array_unshift($this->riwayat, "[$timestamp] $msg");
    }

    // Getter untuk UI
    public function getInfo() {
        return [
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'level' => $this->level,
            'stats' => $this->stats,
            'evs' => $this->evs,
            'moves' => $this->moves,
            'multipliers' => $this->evMultipliers // Untuk debugging display
        ];
    }

    public function getRiwayat() { return $this->riwayat; }
}

// --- 3. INHERITANCE & POLYMORPHISM (Tipe Spesifik) ---

class BugPokemon extends Pokemon {
    public function __construct($nama, $level) {
        parent::__construct($nama, $level);
        $this->tipe = "Bug";
        
        // --- PENYESUAIAN GAYA PELATIHAN ---
        // Tipe Bug fokus pada Speed dan Defense
        $this->evMultipliers = [
            'hp' => 1.0,
            'attack' => 0.8,   // Kurang bakat di attack
            'defense' => 1.5,  // Bonus 50% efektivitas
            'speed' => 2.0     // Bonus 100% (2x lipat) efektivitas
        ];
    }
}

class FirePokemon extends Pokemon {
    public function __construct($nama, $level) {
        parent::__construct($nama, $level);
        $this->tipe = "Fire";
        
        // --- PENYESUAIAN GAYA PELATIHAN ---
        // Tipe Fire fokus pada Attack dan Speed
        $this->evMultipliers = [
            'hp' => 0.9,
            'attack' => 2.0,   // Bonus 2x lipat attack
            'defense' => 0.8,
            'speed' => 1.2
        ];
    }
}

// ==========================================
// LOGIKA CONTROLER
// ==========================================

// Reset
if (isset($_GET['reset'])) { session_destroy(); header("Location: ?"); exit; }

// Inisialisasi (Default: Scyther si Tipe Bug)
if (!isset($_SESSION['pokemon'])) {
    $_SESSION['pokemon'] = new BugPokemon("Scyther", 5);
}
$p = $_SESSION['pokemon'];

// Handler Aksi
if (isset($_POST['action'])) {
    $act = $_POST['action'];
    
    if ($act == 'train_ev') {
        $stat = $_POST['stat_target'];
        $p->trainEV($stat);
    } 
    elseif ($act == 'levelup') {
        $p->levelUp();
    }
    elseif ($act == 'learn_tm') {
        $tmName = $_POST['tm_name'];
        if(!empty($tmName)) $p->learnTM($tmName);
    }
    
    $_SESSION['pokemon'] = $p; // Simpan state
}

$info = $p->getInfo();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Advanced Training System</title>
    <style>
        body { font-family: 'Consolas', sans-serif; background: #222; color: #eee; max-width: 800px; margin: 20px auto; padding: 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: #333; padding: 20px; border-radius: 8px; border: 1px solid #444; }
        h2 { border-bottom: 2px solid #555; padding-bottom: 10px; margin-top: 0; color: #4cd137; }
        .stat-row { display: flex; justify-content: space-between; margin-bottom: 5px; padding: 5px; background: #444; border-radius: 4px;}
        .stat-val { font-weight: bold; color: #fbc531; }
        .multiplier { font-size: 0.8em; color: #00a8ff; }
        
        button { cursor: pointer; padding: 10px; background: #0097e6; color: white; border: none; border-radius: 4px; width: 100%; margin-bottom: 5px; }
        button:hover { background: #00a8ff; }
        input[type="text"] { width: 70%; padding: 10px; box-sizing: border-box; }
        .btn-tm { width: 25%; background: #9c88ff; }
        
        .moves-badge { display: inline-block; background: #e84118; padding: 5px 10px; border-radius: 15px; font-size: 0.8em; margin: 2px; }
        .history { height: 200px; overflow-y: auto; font-size: 0.85em; color: #ccc; }
    </style>
</head>
<body>

    <div class="header" style="text-align:center; margin-bottom:20px;">
        <h1>SISTEM PELATIHAN: <?= $info['nama'] ?> (<?= $info['tipe'] ?>)</h1>
        <p>Level: <?= $info['level'] ?></p>
        <a href="?reset=true" style="color:red;">[Reset / Ganti Pokemon]</a>
    </div>

    <div class="grid">
        <div class="card">
            <h2>📊 Statistik & EV</h2>
            <p style="font-size:0.9em; color:#aaa;">Multiplier menunjukkan efektivitas latihan tipe ini.</p>
            
            <?php foreach($info['stats'] as $key => $val): ?>
            <div class="stat-row">
                <span>
                    <?= ucfirst($key) ?> 
                    <span class="multiplier">(x<?= $info['multipliers'][$key] ?>)</span>
                </span>
                <span class="stat-val"><?= $val ?></span>
            </div>
            <?php endforeach; ?>

            <h3>⚔️ Daftar Jurus (Max 4)</h3>
            <div>
                <?php foreach($info['moves'] as $move): ?>
                    <span class="moves-badge"><?= $move ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2>🏋️ Menu Pelatihan</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="train_ev">
                <p><strong>Fokus EV Training (Sesuaikan Tipe):</strong></p>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:5px;">
                    <button name="stat_target" value="speed">Latih Speed (Rekomendasi Bug)</button>
                    <button name="stat_target" value="defense">Latih Defense</button>
                    <button name="stat_target" value="attack">Latih Attack</button>
                    <button name="stat_target" value="hp">Latih HP / Stamina</button>
                </div>
            </form>

            <hr style="border-color:#555;">

            <form method="POST">
                <input type="hidden" name="action" value="levelup">
                <button style="background:#44bd32;">⬆️ Level Up (General Boost)</button>
            </form>

            <hr style="border-color:#555;">

            <form method="POST">
                <input type="hidden" name="action" value="learn_tm">
                <p><strong>Belajar Jurus Baru (TM/TR):</strong></p>
                <input type="text" name="tm_name" placeholder="Nama Jurus (misal: X-Scissor)">
                <button class="btn-tm">AJARKAN</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>📝 Log Aktivitas</h2>
        <div class="history">
            <?php foreach($p->getRiwayat() as $log): ?>
                <div style="border-bottom:1px solid #444; padding:5px;"><?= $log ?></div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>