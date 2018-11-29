<?php
//Tarkistetaan, että käyttäjä tuli tälle sivulle signup-napin kautta.
if (isset($_POST['signup-submit'])) {
  //Otetaan scripti mukaan
  require 'dbh.inc.php';

  //Otetaan data, jota käyttäjä syöttää "login"-lomakkeella
  $username = $_POST['uid'];
  $email = $_POST['mail'];
  $password = $_POST['pwd'];
  $passwordRepeat = $_POST['pwd-repeat'];

  // Tarkistetaan data signup-sivulta)
  if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
    header("Location: ../signup.php?error=emptyfields&uid=".$username."&mail=".$email);
    exit();
  }
  //Tarkistetaan käyttäjänimi ja s.posti
  else if (!preg_match("/^[a-zA-Z0-9]*$/", $username) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../signup.php?error=invaliduidmail");
    exit();
  }
  // Tarkistetaan,että käyttäjänimi sisältää vain numeroita ja kirjaimia
  else if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
    header("Location: ../signup.php?error=invaliduid&mail=".$email);
    exit();
  }
  //S.posti oikeassa muodossa
  else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../signup.php?error=invalidmail&uid=".$username);
    exit();
  }
  // Salasanat eivät ole samat
  else if ($password !== $passwordRepeat) {
    header("Location: ../signup.php?error=passwordcheck&uid=".$username."&mail=".$email);
    exit();
  }
  //Seuraavaksi tarkistetaan, että tietokannasta ei löydy jo vastaavaa käyttäjänimeä
  else {
    $sql = "SELECT uidUsers FROM users WHERE uidUsers=?;";
    //luodaan prepared statement.Prepared statementit on turvallisempia käyttää sql-injektioita vastaan
    $stmt = mysqli_stmt_init($conn);
    //valmistellaan "SQL statement" ja tarkistetaan, ettei virheitä löydy
    if (!mysqli_stmt_prepare($stmt, $sql)) {
      //Jos virheitä löytyy palautetaan takaisin "signup-sivulle"
      header("Location: ../signup.php?error=sqlerror");
      exit();
    }
    else {
      //Bindataan parametrin tyyppi, jonka oletetaan laitettavan. Tässä tapauksessa se on, s = string
      mysqli_stmt_bind_param($stmt, "s", $username);
      // Lähetetään execute_statement tietokantaan.
      mysqli_stmt_execute($stmt);
      // varastoidaan saatu tulos
      mysqli_stmt_store_result($stmt);
      // Saamme tiedon tietokannasta
      $resultCount = mysqli_stmt_num_rows($stmt);
      // Suljetaan prepared statement
      mysqli_stmt_close($stmt);
      // Tehdään tarkistus, että löytyikö tietokannasta käyttäjänimeä.
      if ($resultCount > 0) {
        header("Location: ../signup.php?error=usertaken&mail=".$email);
        exit();
      }
      else {
        //Jos virhetilanteita ei synny pääsemme lähettämään tiedot tietokantaan.
        //Prepared statementit toimii siten, että ensin lähetetään SQL tietokantaan, jonka jälkeen lomakkeella käytetään "placeholdereita"
        //Kysymysmerkit tarkoitttaa placeholdereita
        $sql = "INSERT INTO users (uidUsers, emailUsers, pwdUsers) VALUES (?, ?, ?);";
        //Luodaan taas uusi statement-tila
        $stmt = mysqli_stmt_init($conn);
        // Tarkistetaan ettei sql:n kanssa ole virhetilanteita
        if (!mysqli_stmt_prepare($stmt, $sql)) {
          // Jos virheitä löytyy, palataan takaisin signup-sivulle.
          header("Location: ../signup.php?error=sqlerror");
          exit();
        }
        else {
          // Jos mitään virhetilanteita ei löydy, voimme jatkaa.
          //Hashataan salasana. Password_DEFAULT käyttää oletuksena BCRYPT-algoritmia.
          $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

          //Bindataan oletetut arvot sss-muodossa(string) käyttäjältä.
          mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPwd);
          //Luodaan execute-tila ja lähetetään tiedot tietokantaan.
          mysqli_stmt_execute($stmt);
          //Jos kirjautuminen onnistui lähetetään käyttäjälle tieto, että kirjautuminen on onnistunut.
          header("Location: ../signup.php?signup=success");
          exit();

        }
      }
    }
  }
  // Voimme sulkea prepared-tilan sekä yhteyden tietokantaan.
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
else {
  // Jos käyttäjä yrittää päästä sisälle väärin keinoin, lähetetään hänet takasisin signup-sivulle.
  header("Location: ../signup.php");
  exit();
}
