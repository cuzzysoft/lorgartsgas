    <?php
                   
session_start();
include('../../db/connect.php');
if(isset($_POST['year'])){
    $year = $_POST['year'];
        $arr = array();
  
    $my2 = mysqli_query($conn, "select distinct month from user_sales where year = '$year'");
    while ($row2 = mysqli_fetch_assoc($my2)) {
        $mt = $row2['month'];
        $mon =  date("F", mktime(0, 0, 0, $mt, 10));
         $arr[] = array("id" => $mt, "real" => $mon);
        
    }
          echo json_encode($arr);
      }

if(isset($_POST['ye'])){
    $year = $_POST['ye'];
    $mon = $_POST['idreall'];
   // $days = cal_days_in_month(CAL_GREGORIAN,$mon,$year);
    
     
     
        $arr = array();
  
                $alin = 0;
                $ale = 0;

                $totte = 0;
                $totti = 0;
                for ($i = 1; $i <= 31; $i++) {
                    $d = $i;
                    if (strlen($i) == 1) {
                        $d = '0' . $i;
                    }
                    $hk = mysqli_query($conn, "select sum(price) as 'su' from expenses where yr= '$year' and month='$mon' and day ='$d' ");
                    if ($kl = mysqli_fetch_assoc($hk)) {
                        $oi = $kl['su'] == "" ? 0 : $kl['su'];
                        $totte += $oi;
                    }
                    $hk = mysqli_query($conn, "select sum(price) as 'su' from purchases where year= '$year' and month='$mon' and day ='$d'");
                    if ($kl = mysqli_fetch_assoc($hk)) {
                        $oi = $kl['su'] == "" ? 0 : $kl['su'];
                        $totte += $oi;
                    }
                    $hk = mysqli_query($conn, "select sum(amount) as 'su' from user_sales  where year= '$year' and month='$mon' and day ='$d'");
                    if ($kl = mysqli_fetch_assoc($hk)) {
                        $totti = $kl['su'] == "" ? 0 : $kl['su'];
                    } 
                    if($totti == 0 && $totte == 0){
                    
                    }else{
                       $arr[] = array("day"=>$d, "toti"=>number_format($totti),"tote"=>number_format($totte)); 
                    }
$totte=0; $totti=0;
                }

    
          echo json_encode($arr);
      }

if(isset($_POST['idreal'])){
    $month = $_POST['idreal'];
    $year = $_POST['years'];
    
    $fdate = $year+"-"+$month+"-"+"01";
    $ldate = $year+"-"+$month+"-"+"31";
    
    $tot = 0;
    $totincome = 0;
    $profit = 0;
    $loss = 0;
   // $mon = ltrim($month, '');

    $hk = mysqli_query($conn, "select sum(price) as 'su' from expenses where yr = '$year' and month = '$month' ");
    if ($kl = mysqli_fetch_assoc($hk)) {
        $tot += $kl['su'];
    }
    $hk = mysqli_query($conn, "select sum(price) as 'su' from purchases  where yr = '$year' and month = '$month' ");
    if ($kl = mysqli_fetch_assoc($hk)) {
        $tot += $kl['su'];
    }
    $hk = mysqli_query($conn, "select sum(amount) as 'su' from user_sales  where year = '$year' and month = '$month' ");
    if ($kl = mysqli_fetch_assoc($hk)) {
        $totincome = $kl['su'];
    }
    $minus =  $totincome - $tot;
    if ($minus >= 0) {
        $profit = $minus;
    } else {
        $loss = $minus;
    }
    
    
     $arr = array("totexpense"=>number_format($tot), "totincome"=>number_format($totincome),"profit"=>number_format($profit), "loss"=>number_format($loss));
    
    
          echo json_encode($arr);
       
    
}

                ?>