<?php

include('../../db/connect.php');
session_start();

header('Content-Type: application/json'); 
$return_arr = array(); 

$userid = $_SESSION['user_id'];

if(isset($_POST['lpg_qty'])){
    
    $qty_liter = $_POST['lpg_qty'];
    $customer = $_POST['customer'];
    $pvia = $_POST['pvia'];
    $sel_acc = $_POST['sel_acc'];
    $dated = $_POST['selldate']; 
    
    $datetime = new DateTime($dated);
    $date = $datetime->format('Y-m-d');
    $dtime = $datetime->format('Y-m-d H:i:s');
    
    
    
    $today = new DateTime();
    $today = $today->format('Y-m-d');

    // Check if selected date is greater than today
    if ($date > $today) {
        $return_arr = array('status' => 'Failed', 'msg' => 'invalid date');
        echo json_encode($return_arr);
        exit;
    }

    $hiddenid = $_POST['hiddenid']; 
    $tprice = str_replace(",", "", $_POST['lpg_price']);

    // Start transaction
    try {
        mysqli_begin_transaction($conn);

        if($hiddenid == ""){
            // NEW SALE LOGIC
            
            // Check tank quantity
            $my = mysqli_query($conn, "SELECT qty, min_qty FROM tanks WHERE id = 26");
            if(!$my){
                throw new Exception("Failed to fetch tank data: " . mysqli_error($conn));
            }
            
            if($row = mysqli_fetch_assoc($my)){
                $min = $row['qty'] - $row['min_qty'];
                
                if($qty_liter > $min){
                    throw new Exception('Insufficient quantity in tank');
                }
                
                // Update tank quantity
                $rem = $row['qty'] - $qty_liter;
                $update_tank = mysqli_query($conn, "UPDATE tanks SET qty = '$rem' WHERE id = 26");
                if(!$update_tank){
                    throw new Exception("Failed to update tank: " . mysqli_error($conn));
                }

                // Insert sale record
                $qntity = $qty_liter . 'Kg';
                list($year, $month, $day) = explode("-", $date);

                $insert_sale = mysqli_query($conn, "INSERT INTO sales (id,user,paid_via,qty,price,date,location,year,day,month,product,buyer,datetime) 
                VALUES (0,'$userid','$pvia','$qntity','$tprice','$date',10,'$year','$day','$month',5,'$customer','$dtime')");
                
                if(!$insert_sale){
                    throw new Exception("Failed to insert sale: " . mysqli_error($conn));
                }
                
                $last_id = mysqli_insert_id($conn);
                $des = 'From the sale of '.$qty_liter.' Kg of Gas';
                
                // Handle payment method
                if($pvia == "Cash"){               
                    $m = mysqli_query($conn, "SELECT cash FROM cash_at_hand WHERE location = 10");
                    if(!$m){
                        throw new Exception("Failed to fetch cash at hand: " . mysqli_error($conn));
                    }
                    
                    if($row = mysqli_fetch_assoc($m)){
                        $cshrem = $tprice + $row['cash'];
                        
                        $update_cash = mysqli_query($conn, "UPDATE cash_at_hand SET cash = '$cshrem' WHERE location = 10");
                        if(!$update_cash){
                            throw new Exception("Failed to update cash at hand: " . mysqli_error($conn));
                        }
                        
                        $insert_history = mysqli_query($conn, "INSERT INTO cash_at_hand_history (id,location,type,amount,description,date,post_balance,sale_id) 
                        VALUES (0,10,'Credit','$tprice','$des','$date','$cshrem','$last_id')");
                        
                        if(!$insert_history){
                            throw new Exception("Failed to insert cash history: " . mysqli_error($conn));
                        }
                    }
                   // mysqli_query($conn, "update sales set payment_date = '$dtime' where id = '$last_id' ");
                }
                else if($pvia == "Unpaid"){
                    // No action needed for unpaid
                }
                else{
                    $insert_transfer = mysqli_query($conn, "INSERT INTO account_transfer (id,account,amount,user,description,sale_id) 
                    VALUES (0, '$sel_acc','$tprice','$userid','$des','$last_id')");
                    
                    if(!$insert_transfer){
                        throw new Exception("Failed to insert account transfer: " . mysqli_error($conn));
                    }
                }
                
                //mysqli_query($conn, "update sales set payment_date = '$dtime' where id = '$last_id' ");

                mysqli_commit($conn);
                $return_arr = array('status' => 'Success', 'msg' => 'Sale made successfully');
                echo json_encode($return_arr);
            }
            else{
                throw new Exception("Tank not found");
            }

        }
        else{
            // UPDATE SALE LOGIC
            
            // Fetch existing sale
            $mo = mysqli_query($conn, "SELECT * FROM sales WHERE id = '$hiddenid'");
            if(!$mo){
                throw new Exception("Failed to fetch sale data: " . mysqli_error($conn));
            }
            
            $row = mysqli_fetch_assoc($mo);
            if(!$row){
                throw new Exception("Sale record not found");
            }
            
            $qtyy = str_replace("Kg", "", $row['qty']);
            $sid = $row['id'];
            
            $previouspayment = $row['paid_via'];
            
            // Restore old quantity to tank
            $my = mysqli_query($conn, "SELECT qty FROM tanks WHERE id = 26");
            if(!$my){
                throw new Exception("Failed to fetch tank data: " . mysqli_error($conn));
            }
            
            $row2 = mysqli_fetch_assoc($my);
            $bl = $row2['qty'] + $qtyy;
            
            $restore_tank = mysqli_query($conn, "UPDATE tanks SET qty = '$bl' WHERE id = 26");
            if(!$restore_tank){
                throw new Exception("Failed to restore tank quantity: " . mysqli_error($conn));
            }
            
            // Reverse old payment
            if($row['paid_via'] == "Cash"){
                $myy = mysqli_query($conn, "SELECT cash FROM cash_at_hand WHERE location = 10");
                if(!$myy){
                    throw new Exception("Failed to fetch cash at hand: " . mysqli_error($conn));
                }
                
                $row3 = mysqli_fetch_assoc($myy);
                $rem = $row3['cash'] - $row['price'];
                
                $update_cash = mysqli_query($conn, "UPDATE cash_at_hand SET cash = '$rem' WHERE location = 10");
                if(!$update_cash){
                    throw new Exception("Failed to update cash at hand: " . mysqli_error($conn));
                }
                
                $delete_history = mysqli_query($conn, "DELETE FROM cash_at_hand_history WHERE sale_id = '$sid'");
                if(!$delete_history){
                    throw new Exception("Failed to delete cash history: " . mysqli_error($conn));
                }
            }
            else if($row['paid_via'] != "Unpaid"){
                $delete_transfer = mysqli_query($conn, "DELETE FROM account_transfer WHERE sale_id = '$sid'");
                if(!$delete_transfer){
                    throw new Exception("Failed to delete account transfer: " . mysqli_error($conn));
                }
            }
            
            // Delete old sale
            $delete_sale = mysqli_query($conn, "DELETE FROM sales WHERE id = '$sid'");
            if(!$delete_sale){
                throw new Exception("Failed to delete old sale: " . mysqli_error($conn));
            }
            
            // Insert new sale
            $my = mysqli_query($conn, "SELECT qty, min_qty FROM tanks WHERE id = 26");
            if(!$my){
                throw new Exception("Failed to fetch tank data: " . mysqli_error($conn));
            }
            
            if($row = mysqli_fetch_assoc($my)){
                $min = $row['qty'] - $row['min_qty'];
                
                if($qty_liter > $min){
                    throw new Exception('Insufficient quantity in tank');
                }
                
                $rem = $row['qty'] - $qty_liter;
                $update_tank = mysqli_query($conn, "UPDATE tanks SET qty = '$rem' WHERE id = 26");
                if(!$update_tank){
                    throw new Exception("Failed to update tank: " . mysqli_error($conn));
                }
        
                $qntity = $qty_liter . 'Kg';
                list($year, $month, $day) = explode("-", $date);
        
                $insert_sale = mysqli_query($conn, "INSERT INTO sales (id,user,paid_via,qty,price,date,location,year,day,month,product,buyer,datetime) 
                VALUES (0,'$userid','$pvia','$qntity','$tprice','$date',10,'$year','$day','$month',5,'$customer','$dtime')");
                
                
                
                if(!$insert_sale){
                    throw new Exception("Failed to insert sale: " . mysqli_error($conn));
                }
                
                $last_id = mysqli_insert_id($conn);
                $des = 'From the sale of '.$qty_liter.' Kg of Gas';
                
                if($pvia == "Cash"){ 
                    $pdate = date("Y-m-d H:i:s");
                    if($previouspayment == "Unpaid"){
                        mysqli_query($conn, "update sales set payment_date = '$pdate' where id = '$last_id'");
                    }
                    $m = mysqli_query($conn, "SELECT cash FROM cash_at_hand WHERE location = 10");
                    if(!$m){
                        throw new Exception("Failed to fetch cash at hand: " . mysqli_error($conn));
                    }
                    
                    if($row = mysqli_fetch_assoc($m)){
                        $cshrem = $tprice + $row['cash'];
                        
                        $update_cash = mysqli_query($conn, "UPDATE cash_at_hand SET cash = '$cshrem' WHERE location = 10");
                        if(!$update_cash){
                            throw new Exception("Failed to update cash at hand: " . mysqli_error($conn));
                        }
                        
                        $insert_history = mysqli_query($conn, "INSERT INTO cash_at_hand_history (id,location,type,amount,description,date,post_balance,sale_id) 
                        VALUES (0,10,'Credit','$tprice','$des','$date','$cshrem','$last_id')");
                        
                        if(!$insert_history){
                            throw new Exception("Failed to insert cash history: " . mysqli_error($conn));
                        }
                    }
                }
                else if($pvia == "Unpaid"){
                    // No action needed
                }
                else{
                    $insert_transfer = mysqli_query($conn, "INSERT INTO account_transfer (id,account,amount,user,description,sale_id) 
                    VALUES (0, '$sel_acc','$tprice','$userid','$des','$last_id')");
                    
                    $pdate = date("Y-m-d H:i:s");
                    if($previouspayment == "Unpaid"){
                        mysqli_query($conn, "update sales set payment_date = '$pdate' where id = '$last_id'");
                    }
                    
                    if(!$insert_transfer){
                        throw new Exception("Failed to insert account transfer: " . mysqli_error($conn));
                    }
                }

                mysqli_commit($conn);
                $return_arr = array('status' => 'Success', 'msg' => 'Sale updated successfully');
                echo json_encode($return_arr);
            }
            else{
                throw new Exception("Tank not found");
            }
        }
        
    } catch(Exception $e) {
        mysqli_rollback($conn);
        $return_arr = array('status' => 'Failed', 'msg' => $e->getMessage());
        echo json_encode($return_arr);
    }
}
?>