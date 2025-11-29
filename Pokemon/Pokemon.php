<?php
require_once 'Interface.php';

abstract class Pokemon implements TrainerInterface {
    protected $nama;
    protected $level = 1;
    protected $maxLevel = 9;
    protected $ability;
    protected $hiddenAbility = ""; // Properti baru
    protected $tipeLabel;
    protected $stats = [];
    protected $multipliers = [];
    protected $riwayat = []; 

    public function __construct($nama, $ability, $statsInput) {
        $this->nama = $nama;
        $this->ability = $ability;
        $this->stats = $statsInput;
    }

    public function training($statTarget) {
        if ($this->level >= $this->maxLevel) {
            echo "\n ⚠️ Pokemon ini sudah mencapai level maksimal ($this->maxLevel).\n";
            return;
        }

        $this->level++;
        $factor = $this->multipliers[$statTarget];
        $gain = 10 * $factor;
        $this->stats[$statTarget] += $gain;

        // --- FITUR UNLOCK ABILITY ---
        $pesanBonus = "";
        if ($this->level == 9) {
            $this->unlockHiddenAbility();
            $pesanBonus = " [ABILITY UNLOCKED!]";
        }

        // Log System
        $jam = date("H:i:s");
        $logPesan = "[$jam] Latihan $statTarget (+$gain). Lv $this->level. $pesanBonus";
        $this->riwayat[] = $logPesan; 

        if ($this->level != 9) { // Agar tidak double echo dengan pesan unlock
            echo "\n   ✅ Latihan sukses! Level naik ke $this->level.\n";
        }
    }

    protected function unlockHiddenAbility() {
        if (!empty($this->hiddenAbility)) {
            echo "\n   🎉🎉🎉 LEVEL MAX TERCAPAI! 🎉🎉🎉\n";
            echo "   Kemampuan tersembunyi telah bangkit!\n";
            echo "   🔓 UNLOCKED: " . $this->hiddenAbility . "\n";
            
            // Update tampilan ability
            $this->ability = $this->ability . " & " . $this->hiddenAbility;
        }
    }

    public function showHistory() {
        echo "\n=== 📜 RIWAYAT LATIHAN ===\n";
        if (empty($this->riwayat)) {
            echo "Belum ada data latihan.\n";
        } else {
            foreach ($this->riwayat as $log) {
                echo $log . "\n";
            }
        }
        echo "==========================\n";
    }

    public function showStatus() {
        echo "\nPROFIL POKEMON (Level $this->level/$this->maxLevel):\n";
        echo " Nama    : " . $this->nama . "\n";
        echo " Tipe    : " . $this->tipeLabel . "\n";
        echo " Ability : " . $this->ability . "\n"; // Ini akan berubah otomatis
        echo " HP: {$this->stats['hp']} | Atk: {$this->stats['attack']} | Def: {$this->stats['defense']} | Spd: {$this->stats['speed']}\n";
    }
}

// Child Classes
class BugPokemon extends Pokemon {
    public function __construct($nama, $ability, $statsInput) {
        parent::__construct($nama, $ability, $statsInput);
        $this->tipeLabel = "BUG";
        $this->multipliers = ['hp'=>1.0, 'attack'=>0.8, 'defense'=>1.5, 'speed'=>2.0];
        $this->hiddenAbility = "Run Away"; // Isi ability rahasia
    }
}

class FirePokemon extends Pokemon {
    public function __construct($nama, $ability, $statsInput) {
        parent::__construct($nama, $ability, $statsInput);
        $this->tipeLabel = "FIRE";
        $this->multipliers = ['hp'=>0.9, 'attack'=>2.0, 'defense'=>0.8, 'speed'=>1.2];
        $this->hiddenAbility = "Inferno (Atk++)"; // Isi ability rahasia
    }
}
?>