<?php
session_start();

$carte = array('2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, 'J' => 10, 'D' => 10, 'R' => 10, 'A' => 11);
$semi = array('F', 'P', 'C', 'Q');

$vincitore = false;
$giocatore_vince = false;
$giocatore_perde = false;

function notifica($msg)
{
    return '<div class="alert alert-info" role="alert">' . $msg . '</div>';
}

function pescaCarta()
{
    global $carte;
    global $semi;

    $tmp['carta'] = array_rand($carte);
    $tmp['seme'] = $semi[array_rand($semi)];

    while (in_array($tmp['carta'] . $tmp['seme'], $_SESSION['carte_usate'])) {
        $tmp['carta'] = array_rand($carte);
        $tmp['seme'] = $semi[array_rand($semi)];
    }

    $_SESSION['carte_usate'][] = $tmp['carta'] . $tmp['seme'];

    return $tmp;
}

function calcolaCarte($mano)
{
    global $carte;

    $conteggio = 0;
    $assi = 0;

    foreach ($mano as $carta) {
        if ($carta['carta'] != 'A') {
            $conteggio += $carte[$carta['carta']];
        } else {
            $assi++;
        }
    }

    for ($x = 0; $x < $assi; $x++) {
        if ($conteggio + 11 > 21) {
            $conteggio += 1;
        } else {
            $conteggio += 11;
        }
    }

    return $conteggio;
}

function verificaVincitore($giocatore, $computer)
{
    global $vincitore, $giocatore_vince, $giocatore_perde;
    if ($vincitore != true) {
        if (calcolaCarte($giocatore) == 21 && calcolaCarte($computer) == 21) {
            print notifica('PARI, IL BANCO VINCE!');
            $vincitore = true;
            $giocatore_perde = true;
        } elseif (calcolaCarte($giocatore) > 21 || calcolaCarte($computer) == 21) {
            print notifica('HAI PERSO!');
            $vincitore = true;
            $giocatore_perde = true;
        } elseif (calcolaCarte($computer) > 21 || calcolaCarte($giocatore) == 21) {
            print notifica('HAI VINTO!');
            $vincitore = true;
            $giocatore_vince = true;
        } else {
            return false;
        }
    }
}

if (isset($_POST['reset'])) {
    session_destroy();
    session_start();
    $_SESSION['carte_usate'] = array();
    $_SESSION['mano_giocatore'] = array();
    $_SESSION['mano_computer'] = array();

    // Carte iniziali del giocatore
    array_push($_SESSION['mano_giocatore'], pescaCarta());
    array_push($_SESSION['mano_giocatore'], pescaCarta());

    // Carte iniziali del banco
    array_push($_SESSION['mano_computer'], pescaCarta());
    array_push($_SESSION['mano_computer'], pescaCarta());
    $vincitore = false;
    $giocatore_vince = false;
    $giocatore_perde = false;
}

if (!isset($_SESSION['mano_giocatore'])) {
    $_SESSION['carte_usate'] = array();

    $mano_giocatore = array();
    $mano_computer = array();

    // Carte iniziali giocatore
    array_push($mano_giocatore, pescaCarta());
    array_push($mano_giocatore, pescaCarta());

    // Carte iniziali banco
    array_push($mano_computer, pescaCarta());
    array_push($mano_computer, pescaCarta());

    $_SESSION['mano_giocatore'] = $mano_giocatore;
    $_SESSION['mano_computer'] = $mano_computer;
}
?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="css/style.css">
        <title>Blackjack!</title>
    </head>
    <body>
        <audio id="card-sound" src="giro_carta.mp3">
            Il tuo browser non supporta l'elemento audio.
        </audio>
        <audio id="win-sound" src="hai_vinto.mp3">
            Il tuo browser non supporta l'elemento audio.
        </audio>
        <audio id="lose-sound" src="hai_perso.mp3">
            Il tuo browser non supporta l'elemento audio.
        </audio>

        <script>
            // Riprodurre il suono della carta quando si preme il tasto "Carta"
            function playCardSound() {
                var cardSound = document.getElementById('card-sound');
                cardSound.play();
            }

            // Riprodurre il suono della vittoria se il giocatore ha vinto
            function playWinSound() {
                var winSound = document.getElementById('win-sound');
                winSound.play();
            }

            // Riprodurre il suono della sconfitta se il giocatore ha perso
            function playLoseSound() {
                var loseSound = document.getElementById('lose-sound');
                loseSound.play();
            }
        </script>

        <div class="container">

        <?php
            if (isset($_POST['hit']) && $vincitore != true) {
                echo '<script>playCardSound();</script>';
                array_push($_SESSION['mano_giocatore'], pescaCarta());
            } elseif (isset($_POST['stand']) && $vincitore != true) {
                while (calcolaCarte($_SESSION['mano_computer']) < calcolaCarte($_SESSION['mano_giocatore'])) {
                    array_push($_SESSION['mano_computer'], pescaCarta());

                    if (calcolaCarte($_SESSION['mano_computer']) == 21 && calcolaCarte($_SESSION['mano_giocatore']) == 21) {
                        print notifica(' PARI, IL BANCO VINCE!');
                        $vincitore = true;
                        $giocatore_perde = true;
                        continue;
                    } elseif (calcolaCarte($_SESSION['mano_computer']) > 21) {
                        print notifica('HAI VINTO!');
                        $vincitore = true;
                        $giocatore_vince = true;
                        continue;
                    } elseif ((calcolaCarte($_SESSION['mano_giocatore']) < calcolaCarte($_SESSION['mano_computer']) || calcolaCarte($_SESSION['mano_computer']) == 21)) {
                        print notifica('HAI PERSO!');
                        $vincitore = true;
                        $giocatore_perde = true;
                        continue;
                    }
                }

                if (calcolaCarte($_SESSION['mano_giocatore']) == calcolaCarte($_SESSION['mano_computer'])) {
                    print notifica('PARI, IL BANCO VINCE!');
                    $vincitore = true;
                    $giocatore_perde = true;
                }

                if ((calcolaCarte($_SESSION['mano_computer']) > calcolaCarte($_SESSION['mano_giocatore'])) && $vincitore != true) {
                    print notifica('HAI PERSO!');
                    $vincitore = true;
                    $giocatore_perde = true;
                }
            }

            verificaVincitore($_SESSION['mano_giocatore'], $_SESSION['mano_computer']);

            if ($giocatore_vince) {
                echo '<script>playWinSound();</script>';
            }

            if ($giocatore_perde) {
                echo '<script>playLoseSound();</script>';
            }
        ?>

        <center><h2 style="font-size: 50px;">Banco</h2></center>

        <div class="card-table">
            <table align="center">
                <tr>
                    <?php $conteggio_computer = 0; ?>
                    <?php foreach ($_SESSION['mano_computer'] as $carta): ?>
                        <td align="center">
                            <?php if ($conteggio_computer != 0 || $vincitore == true): ?>
                                <img src="./carte/<?= $carta['carta'] . $carta['seme'] ?>.gif" /><br>
                                (<?= $carte[$carta['carta']] ?>)
                            <?php else: ?>
                                <img src="./carte/coperta.gif" /><br>
                                (?)
                            <?php endif; ?>
                        </td>
                        <?php $conteggio_computer++; ?>
                    <?php endforeach; ?>
                </tr>
                <?php if ($vincitore == true): ?>
                    <tr>
                        <td colspan="<?= count($_SESSION['mano_computer']) ?>" align="center">Totale: <?= calcolaCarte($_SESSION['mano_computer']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <form method="post">
                <table align="center" style="margin-top: 70px;">
                    <tr>
                        <?php foreach ($_SESSION['mano_giocatore'] as $carta): ?>
                            <td align="center">
                                <img src="./carte/<?= $carta['carta'] . $carta['seme'] ?>.gif" /><br>
                                (<?= $carte[$carta['carta']] ?>)
                            </td>
                        <?php endforeach; ?>
                    </tr>
                        
                    <tr>
                        <td colspan="<?= count($_SESSION['mano_giocatore']) ?>" align="center">Totale: <?= calcolaCarte($_SESSION['mano_giocatore']) ?></td>
                    </tr>

                    <tr height="20"></tr>

                    <tr>
                        <td colspan="<?= count($_SESSION['mano_giocatore']) ?>" align="center"><input type="submit" name="hit" class="btn btn-primary" value="Carta" onclick="playCardSound()" /> || <input type="submit" name="stand" class="btn btn-primary" value="Sto!" /></td>
                    </tr>

                    <tr height="100" valign="middle">
                        <td colspan="<?= count($_SESSION['mano_giocatore']) ?>" align="center"><input type="submit" name="reset" class="btn btn-primary" value="Gioca ancora" /></td>
                    </tr>
                    
                    </table>
                </div>
            </form>

            <center><h2 style="font-size: 50px;">Giocatore</h2></center>
            
        </div>
    </body>
</html>

<?php
    if ($vincitore == true) {
        session_destroy();
    }
?>