<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class MajaController extends Controller
{
    public $h2h = 'DowQGLgsyokvqMsOLbkRqNz4qq6lySnd';

    public function index()
    {
        /*
        In order to run this simulation, please ensure CURL and JSON extenstions are activated in your PHP configuration
        */
        $inquiryURL   = 'https://sakola.zelnara.com/dev/inquiry';      // example: localhost:8000/inquiry.php
        $paymentURL   = 'https://sakola.zelnara.com/dev/payment';      // example: localhost:8001/payment.php
        $reversalURL  = 'https://sakola.zelnara.com/dev/reversal';    // example: localhost:8002/reversal.php
        $secretKey    = 'DowQGLgsyokvqMsOLbkRqNz4qq6lySnd';

        if (!isset($_POST['action'])){
            $_POST['action'] = '';
        }
        echo '<h1>Simulation</h1>';
        switch ($_POST['action']) {
            case 'reversal':
                $tanggalTransaksi = date('YmdHis');
                $fields = array(
                    'action'               => 'reversal',
                    'kodeBank'             => $_POST['kodeBank'],
                    'kodeChannel'          => $_POST['kodeChannel'],
                    'kodeTerminal'         => $_POST['kodeTerminal'],
                    'nomorPembayaran'      => $_POST['nomorPembayaran'],
                    'tanggalTransaksi'     => $tanggalTransaksi,
                    'tanggalTransaksiAsal' => $_POST['tanggalTransaksiAsal'],
                    'idTransaksi'          => $_POST['nomorPembayaran'] . $tanggalTransaksi,
                    'idTagihan'            => $_POST['idTagihan'],
                    'totalNominal'         => $_POST['totalNominal'],
                    'nomorJurnalPembukuan' => $_POST['nomorJurnalPembukuan'],
                    'checksum'             => sha1(
                                                $_POST['nomorPembayaran'].
                                                $secretKey.
                                                $tanggalTransaksi.
                                                $_POST['totalNominal'].
                                                $_POST['nomorJurnalPembukuan']
                                            )
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $reversalURL);
                curl_setopt($ch, CURLOPT_POST, true);
                $headers  = [
                    'Content-Type: application/json'
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $output = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Timeout';
                } else {
                    curl_close($ch);
                    $output = trim($output);
                    $data = json_decode($output,true);
                    if (!$data) {
                        echo 'Invalid Message Format<br>' . htmlentities($output);
                    } else {
                        if ($data['rc'] != '00') {
                            echo 'Reversal failed<br>' . htmlentities($output);
                        } else {
                            echo htmlentities($output);
                            echo '<h1>Payment has been canceled</h1>';
                        }
                    }
                }
                echo '<br /><input type="reset" name="back" value="back" onclick="document.location.href=\'simulation.php\';" />';
                break;
            case 'payment':
                $tanggalTransaksi = date('YmdHis');
                $nomorJurnalPembukuan = uniqid();
                $fields = array(
                    'action'               => 'payment',
                    'kodeBank'             => $_POST['kodeBank'],
                    'kodeChannel'          => $_POST['kodeChannel'],
                    'kodeTerminal'         => $_POST['kodeTerminal'],
                    'nomorPembayaran'      => $_POST['nomorPembayaran'],
                    'tanggalTransaksi'     => $tanggalTransaksi,
                    'idTransaksi'          => $_POST['nomorPembayaran'] . $tanggalTransaksi,
                    'idTagihan'            => $_POST['idTagihan'],
                    'totalNominal'         => $_POST['totalNominal'],
                    'nomorJurnalPembukuan' => $nomorJurnalPembukuan,
                    'checksum'             => sha1(
                                                $_POST['nomorPembayaran'].
                                                $secretKey.
                                                $tanggalTransaksi.
                                                $_POST['totalNominal'].
                                                $nomorJurnalPembukuan
                                            )
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $paymentURL);
                curl_setopt($ch, CURLOPT_POST, true);
                $headers  = [
                    'Content-Type: application/json'
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $output = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Timeout';
                } else {
                    curl_close($ch);
                    $output = trim($output);
                    $data = json_decode($output,true);
                    if (!$data) {
                        echo 'Invalid Message Format<br>' . htmlentities($output);
                    } else {
                        if ($data['rc'] != '00') {
                            echo 'Payment failed<br>' . htmlentities($output);
                        } else {
                            echo htmlentities($output);
                            echo '<h1>'.$data['msg'].'</h1>';
                            echo '<table>';
                            echo '<tr><td>Payment Number</td><td>:</td><td>' . $data['nomorPembayaran'] . '</td></tr>';
                            echo '<tr><td>Name</td><td>:</td><td>' . $data['nama'] . '</td></tr>';
                            echo '<tr><td colspan=2>Informasi</td></tr>';
                            echo '<tr><td>'. $data['informasi'][0]['label_key']  .'</td><td>:</td><td>' . $data['informasi'][0]['label_value'] . '</td></tr>';
                            echo '<tr><td>'. $data['informasi'][1]['label_key']  .'</td><td>:</td><td>' . $data['informasi'][1]['label_value'] . '</td></tr>';
                            echo '<tr><td colspan=2>Rincian</td></tr>';
                            echo '<tr><td>'. $data['rincian'][0]['kode_rincian']  .'</td><td>:</td><td>' . $data['rincian'][0]['nominal'] . '</td></tr>';
                            echo '<tr><td>'. $data['rincian'][1]['kode_rincian']  .'</td><td>:</td><td>' . $data['rincian'][1]['nominal'] . '</td></tr>';
                            echo '<tr><td>Total Amount</td><td>:</td><td>' . $data['totalNominal'] . '</td></tr>';
                            echo '</table>';
                            echo '<form method="post" action="simulation.php"><input type="hidden" name="action" value="reversal" />';
                            echo '<input type="hidden" name="kodeBank" value="' . $fields['kodeBank'] . '" />';
                            echo '<input type="hidden" name="kodeChannel" value="' . $fields['kodeChannel'] . '" />';
                            echo '<input type="hidden" name="kodeTerminal" value="' . $fields['kodeTerminal'] . '" />';
                            echo '<input type="hidden" name="idTagihan" value="' . $data['idTagihan'] . '" />';
                            echo '<input type="hidden" name="totalNominal" value="' . $fields['totalNominal'] . '" />';
                            echo '<input type="hidden" name="nomorPembayaran" value="' . $data['nomorPembayaran'] . '" />';
                            echo '<input type="hidden" name="tanggalTransaksiAsal" value="' . $fields['tanggalTransaksi'] . '" />';
                            echo '<input type="hidden" name="nomorJurnalPembukuan" value="' . $fields['nomorJurnalPembukuan'] . '" />';
                            echo '<br /><input type="submit" name="reversal" value="Cancel Payment" />';
                            echo '</form>';
                        }
                    }
                }
                echo '<br /><input type="reset" name="back" value="back" onclick="document.location.href=\'simulation.php\';" />';
                break;
            case 'inquiry':
                $tanggalTransaksi = date('YmdHis');
                $fields = array(
                    'action'           => 'inquiry',
                    'kodeBank'         => 'BSM',
                    'kodeChannel'      => 'IBANK',
                    'kodeTerminal'     => uniqid(),
                    'nomorPembayaran'  => $_POST['nomorPembayaran'],
                    'tanggalTransaksi' => $tanggalTransaksi,
                    'idTransaksi'      => $_POST['nomorPembayaran'] . $tanggalTransaksi,
                    'checksum'         => sha1(
                                                $_POST['nomorPembayaran'].
                                                $secretKey.
                                                $tanggalTransaksi
                                            )
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $inquiryURL);
                curl_setopt($ch, CURLOPT_POST, true);
                $headers  = [
                    'Content-Type: application/json'
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


                $output = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Timeout';
                } else {
                    curl_close($ch);
                    $output = trim($output);
                    $data = json_decode($output,true);
                    if (!$data) {
                        echo 'Invalid Message Format<br>' . htmlentities($output);
                    } else {
                        if ($data['rc'] != '00') {
                            echo 'Inquiry failed<br>' . htmlentities($output);
                        } else {
                            echo htmlentities($output);
                            echo '<h1>'.$data['msg'].'</h1>';
                            echo '<table>';
                            echo '<tr><td>Nomor Pembayaran</td><td>:</td><td>' . $data['nomorPembayaran'] . '</td></tr>';
                            echo '<tr><td>Nama</td><td>:</td><td>' . $data['nama'] . '</td></tr>';
                            echo '<tr><td colspan=2>Informasi</td></tr>';
                            echo '<tr><td>'. $data['informasi'][0]['label_key']  .'</td><td>:</td><td>' . $data['informasi'][0]['label_value'] . '</td></tr>';
                            echo '<tr><td>'. $data['informasi'][1]['label_key']  .'</td><td>:</td><td>' . $data['informasi'][1]['label_value'] . '</td></tr>';
                            echo '<tr><td colspan=2>Rincian</td></tr>';
                            echo '<tr><td>'. $data['rincian'][0]['kode_rincian']  .'</td><td>:</td><td>' . $data['rincian'][0]['nominal'] . '</td></tr>';
                            echo '<tr><td>'. $data['rincian'][1]['kode_rincian']  .'</td><td>:</td><td>' . $data['rincian'][1]['nominal'] . '</td></tr>';
                            echo '<tr><td>Total Nominal</td><td>:</td><td>' . $data['totalNominal'] . '</td></tr>';
                            echo '</table>';
                            echo '<form method="post" action="simulation.php"><input type="hidden" name="action" value="payment" />';
                            echo '<input type="hidden" name="kodeBank" value="' . $fields['kodeBank'] . '" />';
                            echo '<input type="hidden" name="kodeChannel" value="' . $fields['kodeChannel'] . '" />';
                            echo '<input type="hidden" name="kodeTerminal" value="' . $fields['kodeTerminal'] . '" />';
                            echo '<input type="hidden" name="idTagihan" value="' . $data['idTagihan'] . '" />';
                            echo '<input type="hidden" name="totalNominal" value="' . $data['totalNominal'] . '" />';
                            echo '<input type="hidden" name="nomorPembayaran" value="' . $data['nomorPembayaran'] . '" />';
                            echo '<br /><input type="submit" name="payment" value="Pay Now" />';
                            echo '</form>';
                        }
                    }
                }
                echo '<br /><input type="reset" name="back" value="back" onclick="window.history.go(-1);" />';
                break;
            default:
                echo '<form method="post" action="simulation.php">
            <input type="hidden" name="action" value="inquiry" />
            Payment Number:
            <input type="text" name="nomorPembayaran" />
            <input type="submit" value="Inquiry"/>
            </form>';
        }

    }
    public function inquiry()
    {
        //Configuration - this is example only
$biller_name               = 'Yayasan Baiturrahman Daarul Fikri';
$allowed_ips               = array(
                                '103.219.249.2','54.251.80.131',
                                '127.0.0.1','::1'
                            );

$secret_key                = 'DowQGLgsyokvqMsOLbkRqNz4qq6lySnd';
$allowed_collecting_agents = array('BSM');
$allowed_channels          = array('TELLER', 'IBANK', 'ATM', 'MBANK');
$db_host                   = 'localhost';				// just example DB host: localhost
$db_user                   = 'root';							// just example DB user: xxx
$db_pass                   = '';					// just example DB pass: 1234567
$db_name                   = 'proyek_maja';		// just example DB name: billingsystem
$log_directory             = './logs/'; 				// should be writable


// function debugLog($o) {
//     // this function is intended to write all requests and responses into a file.
//     // it is better if you write into database
//     $file_debug = $GLOBALS['log_directory'] . 'debug-h2h-' . date("Y-m-d") . '.log';
//     ob_start();
//     var_dump(date("Y-m-d h:i:s"));
//     var_dump($o);
//     $c = ob_get_contents();
//     ob_end_clean();

//     $f = fopen($file_debug, "a");
//     fputs($f, "$c\n");
//     fflush($f);
//     fclose($f);
// }

// debugLog('REQUEST: ');
$request = file_get_contents('php://input');
// debugLog($request);
$_JSON = json_decode($request, true);

        // debugLog($_JSON);

$kodeBank         = $_JSON['kodeBank'];
$kodeChannel      = $_JSON['kodeChannel'];
$kodeTerminal     = $_JSON['kodeTerminal'];
$nomorPembayaran  = $_JSON['nomorPembayaran'];
$tanggalTransaksi = $_JSON['tanggalTransaksi'];
$idTransaksi      = $_JSON['idTransaksi'];

// check whether all variables complete?

if (empty($kodeBank) || empty($kodeChannel) || empty($kodeTerminal) || empty($nomorPembayaran) || empty($tanggalTransaksi) || empty($idTransaksi)) {
    $response = json_encode(array(
        'rc'  => '30',
        'msg' => 'Invalid Message Format'
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}

// check whether bank is allowed to make request?
if (!in_array($kodeBank, $allowed_collecting_agents)) {
    $response = json_encode(array(
        'rc'  => '31',
        'msg' => 'Collecting agent is not allowed by '.$biller_name
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}
// check whether delivery channel is allowed
if (!in_array($kodeChannel, $allowed_channels)) {
    $response = json_encode(array(
        'rc'  => '58',
        'msg' => 'Channel is not allowed by '.$biller_name
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}

// check whether inquiry checksum is valid
if (sha1($_JSON['nomorPembayaran'].$secret_key.$_JSON['tanggalTransaksi']) != $_JSON['checksum']) {
    $response = json_encode(array(
        'rc'  => 'NA',
        'msg' => 'H2H Checksum is invalid'
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}

// check whether billing exists
$isBillingFound = true; // Please check into database.
if ($isBillingFound != true) {
    $response = json_encode(array(
        'rc'  => '14',
        'msg' => 'Billing is not found in '.$biller_name
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}
// check whether billing is in payment periode
$isInPaymentPeriod = true; // Please check into database.
if ($isInPaymentPeriod != true) {
    $response = json_encode(array(
        'rc'  => '81',
        'msg' => 'Billing is expired in '.$biller_name
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}
// check whether billing is already paid
$isBillingPaid = false; // Please check into database.
if ($isBillingPaid != false) {
    $response = json_encode(array(
        'rc'  => '88',
        'msg' => 'Billing is already paid in '.$biller_name
    ));
    // debugLog('RESPONSE: ' . $response);
    echo $response;
    exit();
}

$data = array( // Please check into database.
    'rc'              => '00',
    'msg'             => 'Inquiry Succeeded',
    'nomorPembayaran' => $nomorPembayaran,
    'idPelanggan'     => '12345678',
    'nama'            => 'JOHN DOE',
    'totalNominal'    => 1500000,
    'informasi'       => array(
        array('label_key' => 'Info1', 'label_value' => 'Installment'),
        array('label_key' => 'Info2', 'label_value' => 'Januari 2017'),
    ),
    'rincian'         => array(
        array('kode_rincian' => 'TAGIHAN', 'nominal' => 1000000),
        array('kode_rincian' => 'DENDA', 'nominal' => 500000),
    ),
    'idTagihan'       => 'INVOICE-99889988'
);

// pass all checks
// please perform another check if necessary
$response = json_encode($data);
// debugLog('RESPONSE: ' . $response);
header('Content-Type: application/json');
echo $response;
    }

    public function payment()
    {
        echo 'payment';
    }

    public function reversal()
    {
        echo 'reversal';
    }
}
