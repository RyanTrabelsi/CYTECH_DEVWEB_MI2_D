<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $first_name = $_POST['first_name'];
    $dob = $_POST['dob'];
    $position = $_POST['position'];

    $player_data = "$name; $first_name; $dob; $position\n";

    file_put_contents('infoJoueurs.txt', $player_data, FILE_APPEND);

    echo "Joueur bien ajouté au fichier";
    echo "<br><a href='addPlayer.php'>Retourner vers ajout de fichier</a>";
} else {
    echo "Invalide";
}
?>
