<?php
//Tarkistetaan, että käyttäjä tuli tälle sivulle signup-napin kautta.
if (isset($_POST['login-submit'])) {

  //Otetaan scripti mukaan
  require 'dbh.inc.php';

  //Otetaan data käyttäjältä login-lomakkeelta.
  $mailuid = $_POST['mailuid'];
  $password = $_POST['pwd'];


  //Tarkistetaan ettei kentät ole tyhjiä.
  if (empty($mailuid) || empty($password)) {
    header("Location: ../index.php?error=emptyfields&mailuid=".$mailuid);
    exit();
  }
  else {
    //Tehdään kysely tietokantaan ja asetetaan placeholderit.
    $sql = "SELECT * FROM users WHERE uidUsers=? OR emailUsers=?;";
    //ALustetaan uusi "statement" käyttämällä yhteyttä, jonka loimme dbh.inc.php:ssa
    $stmt = mysqli_stmt_init($conn);
    //Valmistellaan SQL-statementti ja tarkistetaan ettei löydy virheitä
    if (!mysqli_stmt_prepare($stmt, $sql)) {
      //Jos löytyy virhe käännytetään käyttäjä takaisin signup-sivulle
      header("Location: ../index.php?error=sqlerror");
      exit();
    }
    else {
      //Jos virheitä ei löydy pääsemme jatkamaan taas.
        //Bindataan datatyyppi, jonka käyttäjän oletetaan laittavan.Tässä tapauksessa se on, s = string
      mysqli_stmt_bind_param($stmt, "ss", $mailuid, $mailuid);
      //Suoritetaan "prepared"-statementti ja lähetetään se tietokantaan.
      mysqli_stmt_execute($stmt);
      //Saamme tiedon
      $result = mysqli_stmt_get_result($stmt);
      //Tallennetaan saatu tulos muuttujaan.
      if ($row = mysqli_fetch_assoc($result)) {
        //Sitten sovitamme yhteen salasanan, jonka saimme käyttäjältä ja salasanan, joka sijaitsee tietokannassa.
        $pwdCheck = password_verify($password, $row['pwdUsers']);
        //Jos salasanat eivät täsmää, annetaan virhe käyttäjälle.
        if ($pwdCheck == false) {
          //Jos löytyy virhe lähetetään käyttäjä takaisin kirjautumaan.
          header("Location: ../index.php?error=wrongpwd");
          exit();
        }
        //Jos salasanat täsmäävät
        else if ($pwdCheck == true) {
          //Seuraavaksi luodaan session-muuttujat ja data näihin tietokannnasta. Jos meillä on session-muuttujat, websivu tietää, että käyttäjä on kirjautunut sisään.
          //Jotta voimmme luoda ja käyttää session-muuttujia täytyy meidän alustaa/käynnistää ne "session_start"-käskyllä.
          session_start();
          // Nyt luomme muuttujat.
          $_SESSION['id'] = $row['idUsers'];
          $_SESSION['uid'] = $row['uidUsers'];
          $_SESSION['email'] = $row['emailUsers'];
          //Nyt käyttäjä on huomattu/rekisteröity kirjautuneena sisään.
          //-->Takaisin index.php sivulle.
          header("Location: ../index.php?login=success");
          exit();
        }
      }
      else {
        header("Location: ../index.php?login=wronguidpwd");
        exit();
      }
    }
  }
  //Voimme sulkea prepared-statementin ja yhteyden tietokantaan.
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
else {
    // Jos käyttäjä yrittää päästä sisälle väärin keinoin, lähetetään hänet takaisin signup-sivulle.
  header("Location: ../signup.php");
  exit();
}
