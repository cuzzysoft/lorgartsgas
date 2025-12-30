<?php

include('../../db/connect.php');
session_start();

header('Content-Type: application/json'); // Ensure the response is always JSON
$return_arr = array();

$userid = $_SESSION['user_id'];

if (isset($_POST['supplier']) && isset($_POST['product']) && isset($_POST['qty']) && isset($_POST['price']) && isset($_POST['psource'])) {
    $supplier = $_POST['supplier'];
    $prod = $_POST['product'];
    $qty = $_POST['qty'];
    $tprice = $_POST['price'];
    $psource = $_POST['psource'];


   


    if ($qty <=0) {
        $return_arr = array('status' => 'Failed', 'msg' => 'Invalid Quantity');
        echo json_encode($return_arr);
        exit;
    } 

    try {
        mysqli_begin_transaction($conn);

        if ($psource == 'cash') {
            $my = mysqli_query($conn, "select cash from cash_at_hand where location = 10");
            if ($row = mysqli_fetch_assoc($my)) {
                if ($tprice > $row['cash']) {
                    $return_arr = array('status' => 'Failed', 'msg' => 'Insufficient cash at hand');
                    echo json_encode($return_arr);
                    exit;
                }
            }
        }

        $my = mysqli_query($conn, "select id, qty, max_qty from tanks where location = 10");
        if ($row = mysqli_fetch_assoc($my)) {
            $maxqty = $row['max_qty'];
            $rqty = $row['qty'];

            $rem = $rqty + $qty;
            if ($rem > $maxqty) {
                $return_arr = array('status' => 'Failed', 'msg' => 'Quantity added is too much for the tank');
                echo json_encode($return_arr);
                exit;
            } else {
                $tankid = $row['id'];
                mysqli_query($conn, "update tanks set qty = '$rem' where location = 10");
            }
        }
$des = 'Paid for the purchase of ' . $qty . ' litres of LPG';
        $date = date("Y-m-d");
        list($year, $month, $day) = explode("-", $date);

        $yt = mysqli_query($conn, "insert into purchases (id,company,product,qty,price,amt_paid,status,truck,driver,date,qtytruck,year,month,day) 
            values (0,'$supplier','$prod', '$qty','$tprice','$tprice','Completed','','','$date',0,'$year','$month','$day')");

        if ($yt) {
            $last_id = mysqli_insert_id($conn);

            mysqli_query($conn, "insert into tank_history (id,purchaseid,qty,user,tankid,date,total_price,paid_status,supplier) 
                values (0,'$last_id','$qty','$userid','$tankid','$date','$tprice','Paid','$supplier')");
        }
        $sourc = $psource == "cash"?'Cash at Hand':'Bank account transfer';
        mysqli_query($conn, "insert into expenses (id,etype,price,description,date,month,user,yr,location,truck,day,source_of_cash) 
        values (0,2,'$tprice','$des','$date','$month','$userid','$year',10,'','$day','$sourc')");
        

        if ($psource == "cash") {
            
            $my = mysqli_query($conn, "select cash from cash_at_hand where location = 10");
            if ($row = mysqli_fetch_assoc($my)) {
                $gh = $row['cash'] - $tprice;
                mysqli_query($conn, "update cash_at_hand set cash = '$gh' where location = 10");
                mysqli_query($conn, "insert into cash_at_hand_history (id, location,type,amount,description,date,post_balance) 
                    values (0,10,'Debit','$tprice','$des','$date','$gh')");
                mysqli_commit($conn);

                $return_arr = array('status' => 'Success', 'msg' => 'Purchase successful');
                echo json_encode($return_arr);
                exit;
            }
        } else {
            mysqli_commit($conn);
            $return_arr = array('status' => 'Success', 'msg' => 'Purchase successful');
            echo json_encode($return_arr);
            exit;
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $return_arr = array('status' => 'Failed', 'msg' => 'Some error occurred: ' . $e->getMessage());
        echo json_encode($return_arr);
        exit;
    }
} else {
    $return_arr = array('status' => 'Failed', 'msg' => 'Missing required POST data');
    echo json_encode($return_arr);
    exit;
}
