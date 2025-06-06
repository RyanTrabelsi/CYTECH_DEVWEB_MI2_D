<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>CY-Orient</title>
  <link href="style.css" rel="stylesheet" type="text/css" />
</head>
<style>
    .image-container {
    margin: 0 auto;
    display: block;
    }
    </style>
    
<body>
  <section class="top-page">
    <header class="header">
    <img src="image/logo.png" alt="Logo">
    <nav class="nav1">
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="presentation.php">Présentation</a></li>
        <li><a href="reserver.php">Réserver</a></li>
        <li><a href="profil.php">Mon compte</a></li>
    </nav>
    <?php if(isset($_SESSION['user_id'])): ?>
        <?php if($_SESSION['contact_type'] === 'admin'): ?>
            <input type="button" class="admin-button" value="Liste des utilisateurs" onclick="window.location.href='userlist.php';">
        <?php endif; ?>
        <input type="submit" value="Se déconnecter" name="logout" id="logout" onclick="window.location.href='logout.php';">
    <?php else: ?>
        <input type="submit" value="S'inscrire" name="inscription" id="inscription" onclick="window.location.href='form.php';">
        <input type="submit" value="Se connecter" name="connexion" id="connexion" onclick="window.location.href='login_form.php';">
    <?php endif; ?>
</header>
  </section>
  <video id="background-video" autoplay loop muted>
    <source src="image/back.mp4" type="video/mp4">
  </video> 
  <section class="corp">
    <h1 class="intro-title">Envie de voyager autrement</h1>
    <h1 class="intro-title2">Orientez-vous vers CY Orient</h1>
    <div class="present">
      <img src="image/logo.png">
    </div>
    <div class="introduction">
      <h2 class="intro">CY Orient est une agence de voyage qui vous permet de voyager les pays de la péninsule Arabique, de l'Afrique du Nord et du Proche-Orient de manière exclusive, Découvrez avec nous les vingt-et-un pays du monde arabe</h1>
    </div>
    
    <div class="image-container" style="margin: 0 auto; display: block;">
      <img src="image/carte.png">
    
      <div class="hotspot zone1">
        <div class="tooltip">Maroc
        <img src="image/maroc.png">
      </div>
      </div>
    
      <div class="hotspot zone2">
        <div class="tooltip">Algérie
        <img src="image/algerie.png">
      </div>
      </div>
      <div class="hotspot zone3">
        <div class="tooltip">Tunisie
        <img src="image/tunisie.png">
        </div>
      </div>
      <div class="hotspot zone4">
        <div class="tooltip">Libye
        <img src="image/libye.svg">
        </div>
      </div>
      <div class="hotspot zone5">
        <div class="tooltip">Égypte
        <img src="image/egypte.png">
        </div>
      </div>
      <div class="hotspot zone6">
        <div class="tooltip">Mauritanie
        <img src="image/mauritanie.png">
        </div>
      </div>
      <div class="hotspot zone7">
        <div class="tooltip">Mali
        <img src="image/mali.png">  
        </div>
      </div>
      <div class="hotspot zone8">
        <div class="tooltip">Tchad
        <img src="image/tchad.png">
        </div>
      </div>
      <div class="hotspot zone9">
        <div class="tooltip">Soudan
        <img src="image/soudan.png">
        </div>
      </div>
      <div class="hotspot zone10">
        <div class="tooltip">Érythrée
        <img src="image/erythree.svg">
        </div>
      </div>
      <div class="hotspot zone11">
        <div class="tooltip">Djibouti
        <img src="image/djibouti.svg">
        </div>
      </div>
      <div class="hotspot zone12">
        <div class="tooltip">Somalie
        <img src="image/somalie.svg">
        </div>
      </div>
      <div class="hotspot zone13">
        <div class="tooltip">Comores
        <img src="image/comores.svg">
        </div>
      </div>
      <div class="hotspot zone14">
        <div class="tooltip">Liban
        <img src="image/liban.png">
        </div>
      </div>
      <div class="hotspot zone15">
        <div class="tooltip">Jordanie
          <img src="image/jordanie.svg">
        </div>
      </div>
      <div class="hotspot zone16">
        <div class="tooltip">Koweït
        <img src="image/koweit.png">
        </div>
      </div>
      <div class="hotspot zone17">
        <div class="tooltip">Arabie Saoudite
        <img src="image/saoudite.png">
        </div>
      </div>
      <div class="hotspot zone18">
        <div class="tooltip">Bahreïn
        <img src="image/bahrein.png">
        </div>
      </div>
      <div class="hotspot zone19">
        <div class="tooltip">Qatar
        <img src="image/qatar.png">
        </div>
      </div>
      <div class="hotspot zone20">
        <div class="tooltip">Émirats Arabes Unis
          <img src="image/emirats.svg">
        </div>
      </div>
      <div class="hotspot zone21">
        <div class="tooltip">Oman
          <img src="image/oman.png">
        </div>
      </div>
    </div>
  </section>
</body>
<footer>
  <p>&copy; 2025 CY Orient. Tous droits réservés.</p>
</footer>
</html>