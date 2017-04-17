<?php 
/**
 * [array_search_partial busca un string en un valor de un array y devuelve el key]
 * @param  [type] $arr     [description]
 * @param  [type] $keyword [description]
 * @return [type]          [description]
 */
function array_search_partial( $arr, $keyword ) {      
    foreach ( $arr as $index => $string ) {      
        if ( strpos( $keyword , $string) !== FALSE )   return $index;
    } //$arr as $index => $string
}


/**
 * [get_total_rules_active Get the total record of active rules]
 * @return [type] [description]
 */
function get_total_rules_active(){
 global $db_;
    $SQL = "SELECT count(*) as TOTAL FROM `block_queue`;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    $row = $result->fetch_assoc()  ;
    return $row[TOTAL];
}
/**
 * [get_rules_db Get array with rules on DB]
 * @return [type] [description]
 */
function get_rules_db( ) {
    global $db_;
    $SQL = "SELECT * FROM sigs_to_block order by sig_name ;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    while ( $row = $result->fetch_assoc() ) {
        $array_tmp[ ] = $row;
    }
    return $array_tmp;
}

/**
 * [get_rules_db Get the info of signature]
 * @return [type] [description]
 */
function get_signature_info_db($sid=NULL ) {
    if (!$sid) return false;
    global $db_;
    $SQL = "SELECT * FROM signature,event  WHERE `sig_sid` = '$sid' AND sig_id=signature ORDER BY timestamp desc LIMIT 1;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    $row = $result->fetch_assoc();
    return $row;
}

/**
 *
 * TODO: Mejorar consulta SQL
 * 
 * [get_rules_db Get the header info of conextion]
 * @return [type] [description]
 */
function get_header_info_db($cid=NULL ) {
    if (!$cid) return false;
    global $db_;
    $SQL = "SELECT *,inet_ntoa(ip_src) as ip_src,inet_ntoa(ip_dst) as ip_dst  FROM `iphdr` WHERE `cid` = '$cid' LIMIT 1;";
    if ( !$result = $db_->query( $SQL ) )  die( 'There was an error running the query [' . $db_->error . ']' ); 
    //!$result = $db_->query( $SQL ) 
    $row['packet']=$result->fetch_assoc();
    $SQL = "SELECT * FROM `udphdr` WHERE `cid` = '$cid' LIMIT 1;";
    if ( !$result = $db_->query( $SQL ) )  die( 'There was an error running the query [' . $db_->error . ']' ); 
    //!$result = $db_->query( $SQL )
    
    if ($result->num_rows)  $row['port'] = $result->fetch_assoc();
    else {
    $SQL = "SELECT * FROM `tcphdr` WHERE `cid` = '$cid' LIMIT 1;";
     if ( !$result = $db_->query( $SQL ) )  die( 'There was an error running the query [' . $db_->error . ']' ); //!$result = $db_->query( $SQL )
         $row['port'] = $result->fetch_assoc();
    }

    return $row;
 
}

function obtiene_server_status() {
        // UPTIME
        if (false === ($str = @file("/proc/uptime"))) return false;
        $str = explode(" ", implode("", $str));
        $str = trim($str[0]);
        $min = $str / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));
        if ($days !== 0) $res['uptime'] = $days." Days ";
        if ($hours !== 0) $res['uptime'] .= $hours." Hours ";
        $res['server_uptime'] .= $min." Minutes ";

        // MEMORY
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);

        $res['memTotal'] = round($buf[1][0], 2);
        $res['memFree'] = round($buf[2][0], 2);
        $res['memCached'] = round($buf[3][0], 2);
        $res['memUsed'] = ($res['memTotal']-$res['memFree']);
        $res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;
        $res['memRealUsed'] = ($res['memTotal'] - $res['memFree'] - $res['memCached']);
        $res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0;

        $res['swapTotal'] = round($buf[4][0], 2);
        $res['swapFree'] = round($buf[5][0], 2);
        $res['swapUsed'] = ($res['swapTotal']-$res['swapFree']);
        $res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;

        // LOAD AVG
        if (false === ($str = @file("/proc/loadavg"))) return false;
        $str = explode(" ", implode("", $str));
        $str = array_chunk($str, 4);
        $res['loadAvg'] = implode(" ", $str[0]);

        
        $res['memTotal']=filesize_format($res['memTotal']*1024);
        $res['memUsed']=filesize_format($res['memUsed']*1024);
        $res['memCached']=filesize_format($res['memCached']*1024);

        $res['swapTotal']=filesize_format($res['swapTotal']*1024,'','GB');
        $res['swapFree']=filesize_format($res['swapFree']*1024);
        $res['swapUsed']=filesize_format($res['swapUsed']*1024);


        return $res;
}

  // * Format a number of bytes into a human readable format.
  // * Optionally choose the output format and/or force a particular unit

function filesize_format($size,$level=0,$precision=2,$base=1024) {
        $unit = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB','YB');
        $times = floor(log($size,$base));
        return sprintf("%.".$precision."f",$size/pow($base,($times+$level)))." ".$unit[$times+$level];
}


//Facebook like
function format_fecha( $time ) {
    if ( $time !== intval( $time ) ) {
        $time = strtotime( $time );
    }
    $d = time() - $time;
    if ( $time < strtotime( date( 'Y-m-d 00:00:00' ) ) - 60 * 60 * 24 * 3 ) {
        $format = 'F j';
        if ( date( 'Y' ) !== date( 'Y', $time ) ) {
            $format .= ", Y";
        }
        return date( $format, $time );
    }
    if ( $d >= 60 * 60 * 24 ) {
        $day = 'Ayer';
        if ( date( 'l', time() - 60 * 60 * 24 ) !== date( 'l', $time ) ) {
            $day = date( 'l', $time );
        }
        return $day . " a las " . date( 'g:ia', $time );
    }
    if ( $d >= 60 * 60 * 2 ) {
        return intval( $d / ( 60 * 60 ) ) . " hours ago";
    }
    if ( $d >= 60 * 60 ) {
        return "1 hour ago";
    }
    if ( $d >= 60 * 2 ) {
        return intval( $d / 60 ) . " minutes ago";
    }
    if ( $d >= 60 ) {
        return "a minute ago";
    }
    if ( $d >= 2 ) {
        return intval( $d ) . " seconds";
    }
    return "a few seconds ago";
}

// function get_server_cpu_usage(){

//     $load = sys_getloadavg();
//     return $load[0];

//}
function get_server_uptime(){

   $exec_uptime = preg_split("/[\s]+/", trim(shell_exec('uptime')));
    $uptime = $exec_uptime[2] . ' Days';
    return $uptime;

}

// function get_server_status(){

//    $exec_uptime = "echo 'pacotilla' | sudo -H -u root bash -c 'service barnyard2 stop'";
//    // $exec_uptime = "service barnyard2 start";
//    // $exec_uptime = "/etc/init.d/barnyard2 start";
//    $uptime= exec($exec_uptime);
//    // echo shell_exec('whoami');
//     return $uptime;

// }

function check_connect_router_API() {
    global $router;
    require( '/opt/ips-mikrotik-suricata/share/routeros_api.php' );
    $API = new RouterosAPI();
    if ( $API->connect( $router[ 'ip' ], $router[ 'user' ], $router[ 'pass' ] ) ) return "<span class='label label-success '>OK</span>";
    else return( 'Unable to connect to RouterOS. Error:' . $e ); 
    $API->disconnect();
     
}

function check_service_running($service="ids") {
    global $PID_app_file;
    if ($service=="ids") $cmd="suricata -c /etc/suricata/suricata.yaml";
    elseif ($service=="db") $cmd="barnyard2 -c /etc/suricata/barnyard2.conf";
    elseif ($service=="snorby") $cmd="ruby script/rails server -e production -d -b 0.0.0.0";
    elseif ($service=="ips")  if (file_exists($PID_app_file)) return "<span class='label label-success'>OK</span>" ; else return "<span class='label label-danger'>NO</span>";
    $cmd_exec="ps ax | grep -v grep | grep '$cmd' | wc -l";
    // echo $cmd_exec;
    $ret=exec($cmd_exec);
    // return $ret;
   if  ($ret)  return "<span class='label label-success '>OK</span>" ; else return "<span class='label label-danger lead'>NO</span>";
}
?>