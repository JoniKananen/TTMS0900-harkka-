<?php
  // Header-osio on jo kertaalleen tehty, niinpä meidän ei tarvitse luoda sitä uudestaan jokaiselle pätkälle vaan lisäämme sen mukaan "require"-käskyllä
  require "header.php";
?>
    <main>
      <div class="wrapper-main">
        <section class="section-default">
          <!--
          Näytetään "logged in" tai "Logged out" riippuen siitä kumpi on tapahtunut.
          -->
          <?php
          if (!isset($_SESSION['id'])) {
            echo '<p class="login-status">You are logged out!</p>';
          }
          else if (isset($_SESSION['id'])) {
            echo '<p class="login-status">You are logged in!</p>';
          }
          ?>
        </section>
      </div>
    </main>

<?php
  //Sama juttu, kuin header-osion kanssa, voimme vain lisätä sen.
  require "footer.php";
?>
