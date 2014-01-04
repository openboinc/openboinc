<?php
if(!file_exists('status.db')) {
    try {
        $dbh=new PDO('sqlite:status.db');
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->exec('
            CREATE TABLE status(
                server TEXT ,
                status TEXT ,
                time integer ,
                PRIMARY KEY(server)
            )');
    } catch (Exception $e) {
        echo "error!!:$e";
        exit;
    }
    echo "db created successfully!";
}
elseif (!isset($_GET['create'])) {
    $dbh=new PDO('sqlite:status.db');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = $dbh->query("SELECT * FROM status");
    $result = $sql->fetchAll();
    echo "<table>";
    echo "<tr><th>Server</th><th>Status</th><th>Last Time</th></tr>";
    for ($i = 0,$c = count($result); $i < $c; $i++) {
        if ($result[$i]['status']=='off'||round((time()-(int)$result[$i]['time'])/60)>61) {
            echo "<tr bgcolor=\"red\">";
        }
        else
        {
            echo "<tr>";
        }
        echo "<td>".
            $result[$i]['server'].
            "</td><td>".
            $result[$i]['status'].
            "</td><td>".
            (string)round((time()-(int)$result[$i]['time'])/60).
            "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
else
{
    $status = $_GET['status'];
    $time = $_GET['time'];
    $server = $_GET['server'];
    $dbh=new PDO('sqlite:status.db');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = $dbh->query("SELECT server FROM status");
    $result = $sql->fetchAll();
    $result_arr = array();
    for ($i = 0,$c=count($result); $i < $c; $i++) {
        array_push($result_arr,$result[$i]["server"]);
    }
    if (in_array($_GET['server'],$result_arr)) {
        if ($status =='off') {
            try
            {
                $dbh -> exec("UPDATE status SET status='$status' WHERE server=$server");
            }
            catch(Exception $e) {
                echo $e->getMessage();
            }
        }else{
            $dbh -> exec("UPDATE status SET status='$status', time=$time WHERE server=$server");
        }
    }
    else{
        $dbh -> exec("INSERT INTO status(server, status, time) VALUES('$server','$status',$time)");
    }
}
?>
