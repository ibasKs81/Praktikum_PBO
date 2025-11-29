<?php
require_once 'Interface.php';
require_once 'Pokemon.php';

$namaPokemon = "caterpie";
$ability     = "dust shield";

$statsAwal   = [
    'hp'      => 45, 
    'attack'  => 30, 
    'defense' => 35, 
    'speed'   => 20
];

// katena caterpie adalah tipe bug maka gunakan class BugPokemon
$myPokemon = new BugPokemon($namaPokemon, $ability, $statsAwal);


// agar bisa input dari terminal
function input($p) { echo $p; return trim(fgets(STDIN)); }

// --- LOOP MENU UTAMA (Outer Loop) ---
while (true) {
    echo "\n=== MENU UTAMA ===\n";
    echo "[1] Masuk Mode Latihan\n";
    echo "[2] Lihat Log Riwayat Latihan\n";
    echo "[0] Keluar Aplikasi\n";
    
    $mainMenu = input("Pilih menu utama > ");

    if ($mainMenu == "0") {
        echo "Sampai jumpa!\n";
        break; // Keluar dari program
    }
    
    elseif ($mainMenu == "2") {
        // Tampilkan Log
        $myPokemon->showHistory();
        input("\n[Tekan Enter kembali ke menu utama]");
    }
    
    elseif ($mainMenu == "1") {
        
        // --- LOOP MODE LATIHAN (Inner Loop) ---
        while (true) {
            system('cls'); // Bersihkan layar biar rapi
            $myPokemon->showStatus();

            echo "\n--- PILIH JENIS LATIHAN (Tipe: BUG) ---\n";
            echo "[1] Latihan HP      (Normal)\n";
            echo "[2] Latihan Attack  (Lambat)\n";
            echo "[3] Latihan Defense (Cepat x1.5)\n";
            echo "[4] Latihan Speed   (Sangat Cepat x2.0)\n";
            echo "[9] << Kembali ke Menu Utama\n";
            
            $pilihan = input("\nPilih latihan > ");

            if ($pilihan == "9") {
                break; // Keluar dari Loop Latihan, kembali ke Loop Utama
            }

            // Eksekusi Latihan
            switch ($pilihan) {
                case "1": $myPokemon->training('hp'); break;
                case "2": $myPokemon->training('attack'); break;
                case "3": $myPokemon->training('defense'); break;
                case "4": $myPokemon->training('speed'); break;
                default: echo "Pilihan tidak valid.\n";
            }

            // --- FITUR KONFIRMASI (User Request) ---
            echo "\n----------------------------------\n";
            echo "Opsi selanjutnya:\n";
            echo "[Enter] Lanjut latihan lagi\n";
            echo "[M]     Kembali ke Main Menu\n";
            $next = input("Pilihan > ");
            
            if (strtolower($next) == 'm') {
                break; 
            }

        }
    } 
    else {
        echo "Menu tidak dikenali.\n";
    }
}