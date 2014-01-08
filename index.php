<?php
$filename = "status";
if(!file_exists($filename)) {
    $result=array(
        //'server1'=>array('status'=>'off','time'=>1234567891),
        //'server2'=>array('status'=>'on','time'=>1234567991),
        //'server3'=>array('status'=>'off','time'=>1234587891)
    );
    $handle = fopen($filename,'a');
    fwrite($handle,serialize($result));
    fclose($handle);
}
if (!isset($_GET['create'])) {
    $handle = fopen($filename, "r");
    if(flock($handle , LOCK_EX)){    
        $contents = fread($handle, filesize($filename));
        flock($handle , LOCK_UN);    
    }
    fclose($handle);
    $result = unserialize($contents);
    echo "<table>";
    echo "<tr><th>Num</th><th>Server</th><th>Status</th><th>Last Time</th></tr>";
    $num = 0;
    foreach ($result as $server => $status) {
        $num = $num+1;
        if ($status['status']=='off'||round((time()-(int)$status['time'])/60)>61) {
            echo "<tr bgcolor=\"red\">";
        }
        else
        {
            echo "<tr bgcolor=\"lightblue\">";
        }
        echo "<td align=\"center\">".
            (string)$num.
            "</td>";
        echo "<td align=\"center\">".
            $server.
            "</td><td align=\"center\">".
            $status['status'].
            "</td><td align=\"center\">".
            (string)round((time()-(int)$status['time'])/60).
            "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br>";
    if(!file_exists("../.openshift/cron/hourly/jobs.allow")) {
        exec('touch $OPENSHIFT_REPO_DIR.openshift/cron/hourly/jobs.allow && echo http_activate > $OPENSHIFT_REPO_DIR.openshift/cron/hourly/jobs.allow && chmod +x $OPENSHIFT_REPO_DIR.openshift/cron/hourly/jobs.allow');
    }
    exec('rm $OPENSHIFT_REPO_DIR.openshift/cron/hourly/http_activate');
    exec('touch $OPENSHIFT_REPO_DIR.openshift/cron/hourly/http_activate');
    foreach ($result as $server => $status) {
        $url = explode('@',$server)[1];
        exec('echo wget -q "'.$url.'" >>$OPENSHIFT_REPO_DIR.openshift/cron/hourly/http_activate');
    }
    exec('echo rm index.html* >>$OPENSHIFT_REPO_DIR.openshift/cron/hourly/http_activate');
    exec('chmod +x $OPENSHIFT_REPO_DIR.openshift/cron/hourly/http_activate');
}
else {
    $handle = fopen($filename, "r");
    if(flock($handle , LOCK_EX)){    
        $contents = fread($handle, filesize($filename));
        flock($handle , LOCK_UN);    
    }
    fclose($handle);
    $result = unserialize($contents);
    $status = $_GET['status'];
    $time = $_GET['time'];
    $server = $_GET['server'];
    $result[$server] = array('status'=>$status,'time'=>$time);
    $handle = fopen($filename, "w");
    if(flock($handle , LOCK_EX)){    
        fwrite($handle,serialize($result));
        flock($handle , LOCK_UN);    
    }
    fclose($handle);
}
?>
