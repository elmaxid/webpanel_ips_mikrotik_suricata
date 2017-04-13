<?php
/*****************************
 *
 * WebPanel for Manager Alerts Rules for IPS MikroTik Suricata  
 *
 * This file is the webgui for update and manager rules of project:
 *
 * https://github.com/elmaxid/ips-mikrotik-suricata *
 * 
 * Author: Maximiliano Dobladez info@mkesolutions.net
 *
 * http://maxid.com.ar | http://www.mkesolutions.net  
 *
 *
 * LICENSE: GPLv2 GNU GENERAL PUBLIC LICENSE
 *
 * 
 * v1.0 - 13 April 17 - initial version
 ******************************/
error_reporting( E_ALL );
// error_reporting( 0 );
//include the config DB and API.
include '/opt/ips-mikrotik-suricata/config.php';
$url_update_rules = 'https://www.update.rules.mkesolutions.net/update.php?c=update';
/* Wait for a connection to the database */
$i                = 0;
while ( $i < 100 ) {
    $db_ = new mysqli( $server, $user_name, $password, $database );
    if ( $db_->connect_errno > 0 ) {
        print( 'Unable to connect to database [' . $db_->connect_error . ']' );
        sleep( 10 );
        $i = $i + 10;
    } //$db_->connect_errno > 0
    else {
        $i          = 100;
        $connect_DB = true;
    }
} //$i < 100
if ( isset( $_REQUEST[ 'c' ] ) )
    $cmd = trim( $_REQUEST[ 'c' ] ); //command
if ( isset( $_REQUEST[ 'updates_rules' ] ) )
    $updates_rules = $_REQUEST[ 'updates_rules' ]; //updates_rules
if ( isset( $_REQUEST[ 'id' ] ) )
    $id = trim( $_REQUEST[ 'id' ] ); //id
if ( isset( $_REQUEST[ 'sig_name' ] ) )
    $sig_name = trim( $_REQUEST[ 'sig_name' ] ); //sig_name
if ( isset( $_REQUEST[ 'src_or_dst' ] ) )
    $src_or_dst = trim( $_REQUEST[ 'src_or_dst' ] ); //src_or_dst
if ( isset( $_REQUEST[ 'timeout' ] ) )
    $timeout = trim( $_REQUEST[ 'timeout' ] ); //timeout
$active = trim( $_REQUEST[ 'active' ] ); //active
if ( $cmd == "edit_rule_save" ) {
    ( $active == "on" ) ? $active_tmp = 1 : $active_tmp = 0;
    if ( $id == "new" )
        $sql_query = "INSERT INTO   sigs_to_block ( active, sig_name, src_or_dst,timeout )
                    VALUES ( '$active','$sig_name','$src_or_dst','$timeout' )";
    else
        $sql_query = "UPDATE sigs_to_block SET active='$active_tmp', sig_name='$sig_name', src_or_dst='$src_or_dst', timeout='$timeout' WHERE id=$id ;";
    if ( !$result = $db_->query( $sql_query ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } else {
        echo '<div class="alert alert-success"> <strong>OK Saved</strong> <i class="fa fa-refresh fa-spin"></i> Reloading... </div>';
    }
    exit;
} elseif ( $cmd == "save_rule_db" ) { // Save the update rules to DB
    //  echo "SAVE";
    // var_dump($updates_rules);
    foreach ( $updates_rules as $value ) {
        $row_tmp   = explode( '##', $value );
        // echo $value;
        $sql_query = "INSERT INTO   sigs_to_block ( sig_name, src_or_dst,timeout )
                         VALUES ( '$row_tmp[0]','$row_tmp[1]','$row_tmp[2]' )";
        echo $sql_query;
        if ( !$result = $db_->query( $sql_query ) ) {
            // die( 'There was an error running the query [' . $db_->error . ']' );
            echo $db_->error;
        }
    }
    // echo show_rules_db(); 
    exit;
} elseif ( $cmd == "list_rule" ) {
    // echo "HOLA";
    echo show_rules_db();
    exit;
} elseif ( $cmd == "delete" ) {
    if ( !$id )
        return false;
    $SQL = "DELETE FROM sigs_to_block WHERE  id='$id'  ;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    mysqli_free_result( $result );
    echo show_rules_db(); //show again the list rules
    exit;
} elseif ( $cmd == "add" ) {
    echo show_form_edit_rule();
    exit;
} elseif ( $cmd == "edit" ) {
    echo show_form_edit_rule( $id );
    exit;
} elseif ( $cmd == "update" ) {
    echo get_update_rules();
    exit;
} elseif ( $cmd == "dashboard" ) {
    echo show_dashboard();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rules Administrator For IPS MikroTik Suricata</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <link href="http://fontawesome.io/assets/font-awesome/css/font-awesome.css" rel="stylesheet" media="screen">
    <link href="a.css" rel="stylesheet" media="screen">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
</head>

<body>
    <!-- jQuery -->
    <script src="//code.jquery.com/jquery.js"></script>
    <!-- Bootstrap JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->

    <nav class="navbar navbar-default navbar-fixed-top topbar">
        <div class="container-fluid">

            <div class="navbar-header">

                <a href="#" class="navbar-brand">
                    <span class="visible-xs">RM</span>
                    <span class="hidden-xs">Rules Manager</span>
                </a>

                <p class="navbar-text">
                    <a href="#" class="sidebar-toggle">
                        <i class="fa fa-bars"></i>
                    </a>
                </p>

            </div>

        </div>
    </nav>

    <article class="wrapper">

        <aside class="sidebar">
            <ul class="sidebar-nav">
                <li><a onclick="get_data('?c=dashboard','central');" href="#"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="#" onclick="get_data('?c=list_rule','central');"><i class="fa fa-exchange"></i> <span>Rules Editor</span></a></li>

            </ul>
        </aside>

        <section class="main">

            <section class="tab-content">

                <section class="tab-pane active fade in content">
                    <div id="central">
                    <?php
                        echo show_dashboard();
                        ?>
                    </div>
                </section>

            </section>

        </section>

    </article>

    <script type="text/javascript">
      function get_data(a,b){if(null==b)var c="central";else var c=b;$.get(a,function(a){""!=a&&$("#"+c).html(a)})}$(document).on("click",".sidebar-toggle",function(){$(".wrapper").toggleClass("toggled")});
    </script>
</body>

</html>


<?php
function show_rules_db( ) {
    global $db_;
    $SQL = "SELECT * FROM sigs_to_block  LIMIT 100;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    $count = $result->num_rows;
    $str .= ' <div class="row">
                       
                         
                       
                       <div class="col-xs-12 col-sm-9">
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                   Alerts Rules (' . $count . ') <a  onclick="get_data(\'?c=add\',\'central\');" href="#" ><i class="fa fa-plus-circle"></i></a>
                               </div>
                               <div class="panel-body">
                                   <table class="table table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th></th> <th>Rule</th> <th>IP Block</th><th>Timeout</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody>   ';
    while ( $row = $result->fetch_assoc() ) {
        ( $row[ 'active' ] ) ? $color_str = 'success' : $color_str = 'info';
        $str .= '<tr><td><span class="label label-' . $color_str . '"><i class="fa fa-check"></i></span></td><td onclick="get_data(\'?c=edit&id=' . $row[ 'id' ] . '\',\'central\');" >' . $row[ 'sig_name' ] . '</td><td>' . $row[ 'src_or_dst' ] . '</td><td>' . $row[ 'timeout' ] . '</td><td> <a onclick="get_data(\'?c=edit&id=' . $row[ 'id' ] . '\',\'central\');"  href=# >  <i class="fa fa-edit"></i> </a> <a onclick="get_data(\'?c=delete&id=' . $row[ 'id' ] . '\',\'central\');"  href=# > <i class="fa fa-trash"></i></a></td></tr>';
    }
    $str .= '
                                    </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                       
                       <div class="col-xs-12 col-sm-3">
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                   Update Channel
                               </div>
                               <div class="panel-body">
                                   
                                   <a href=# onclick="get_data(\'?c=update\',\'central\');" ><i class="fa fa-refresh"></i> Update Rules</a>
                               </div>
                           </div>
                           
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                MKE Solutions
                               </div>
                               <div class="panel-body">
                                   Designed by <a href="http://maxid.com.ar" target="_blank">Maximiliano Dobladez</a></a>
                               </div>
                           </div>
                       </div>
                       
                   </div>';
    return $str;
}
function show_form_edit_rule( $id = NULL ) {
    global $db_;
    if ( !$id ) {
        $new       = true;
        $str_input = '<input type=hidden name="id" value="new">';
    } else {
        $SQL = "SELECT * FROM sigs_to_block  WHERE id=$id LIMIT 1;";
        if ( !$result = $db_->query( $SQL ) ) {
            die( 'There was an error running the query [' . $db_->error . ']' );
        } //!$result = $db_->query( $SQL )
        $row            = $result->fetch_assoc();
        $str_input      = '<input type=hidden name="id" value="' . $id . '">';
        $str_sig_name   = 'value="' . $row[ sig_name ] . '"';
        $str_src_or_dst = '<option value="' . $row[ src_or_dst ] . '" >' . $row[ src_or_dst ] . '</option>';
        $str_timeout    = 'value="' . $row[ timeout ] . '"';
        ( $row[ 'active' ] == 1 ) ? $str_active = "checked" : $str_active = '';
    }
    $str .= '
                    <div class="col-xs-12 col-sm-9">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                              
                                &nbsp;
                            </div>
                            <div class="panel-body">
                             <span id="show_result"></span>

                                <form class="form-horizontal" role="form" autocomplete=off  method="post" id="edit" >
                        ' . $str_input . '
                                    <fieldset>
                                        <legend>Add New Alert Rule</legend>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label" for="name">Name Alert</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" required ' . $str_sig_name . ' name="sig_name" autofocus id="name" placeholder="Name Alert">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label" for="src_or_dst">Target IP to Block</label>
                                            <div class="col-sm-6">
                                                <select name="src_or_dst" id="src_or_dst" class="form-control">
                                                ' . $str_src_or_dst . '
                                                    <option value="src">src</option>
                                                    <option value="dst">dst</option>
                                                </select>

                                            </div>

                                        </div>

                                        <div class="form-group">

                                            <label class="col-sm-3 control-label" for="timeout">Timeout </label>
                                            <div class="col-sm-3">
                                                <input type="text" class="form-control" ' . $str_timeout . ' name="timeout" value="01:01:01">
                                            </div>

                                            <div class="col-sm-2 ">
                                                <label for="active">Active
                                                    <input type="checkbox" name="active" ' . $str_active . ' value=on id="active">
                                                </label>
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-9">
                                                  <a onclick="get_data(\'?c=list_rule\',\'central\');" class="btn btn-default btn-lg"><i class="fa fa-backward"></i> Back</a>
                                                <button type="submit" id=save_btn class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save</button>

                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>

                <script type="text/javascript">
                        $(document).ready(function() {
                            $(\'#save_btn\').click(function(e) {                                 
                                var dataS = $(\'form#edit\').serialize();
                                e.preventDefault();
                                $.ajax({
                                    type: "POST",
                                    url: \'index.php?c=edit_rule_save\',
                                    data: dataS,
                                    success: function(data) {
                                          $(\'#show_result\').html(data) ;
                                      
                                          setTimeout("get_data(\'?c=list_rule\',\'central\')", 1000);                                       
                                    }
                                })
                                return false;
                            });
                        });

                        function sleep(milliseconds) {
                          var start = new Date().getTime();
                          for (var i = 0; i < 1e7; i++) {
                            if ((new Date().getTime() - start) > milliseconds){
                              break;
                            }
                          }
                        }

                    </script>
                  ';
    return $str;
}
/**
 * [get_rules_db Get array with rules on DB]
 * @return [type] [description]
 */
function get_rules_db( ) {
    global $db_;
    $SQL = "SELECT * FROM sigs_to_block ;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    while ( $row = $result->fetch_assoc() ) {
        $array_tmp[ ] = $row;
    }
    return $array_tmp;
}
/**
 * [get_update_rules Get the last update rule from cloud]
 * @return [type] [description]
 */
function get_update_rules( ) {
    global $url_update_rules;
    $update       = file_get_contents( $url_update_rules );
    $update_array = json_decode( $update, true );
    $db_rules     = get_rules_db();
    // echo var_dump($db_rules);
    // echo var_dump($update_array);
    $str .= '<div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Rules Update</h3>
        </div>
        <div class="panel-body">
            <div class="col-md-10">
           <form class="form-horizontal"  role="form" autocomplete=off  method="post" id="update_rules" >
            ';
    foreach ( $update_array as $value ) {
        if ( array_search( $value[ sig_name ], array_column( $db_rules, 'sig_name' ) ) ) {
            // if (in_array($value[sig_name], $db_rules)) {
            // $str.="EXISTE ".$value[sig_name]."<br>";
            $value_tmp = '';
        } else {
            $value_tmp = "$value[sig_name]##$value[src_or_dst]##$value[timeout]";
            $str .= " 

             <div class='form-group'>   <label for='$value[sig_name]' >
                    <input type=checkbox checked name='updates_rules[]' value='$value_tmp'> $value[sig_name]
                    </label>
                    </div>";
            //$str.="NUEVO ".$value[sig_name]."<br>";
        }
    }
    $str .= '  <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-9">
                                                  <a onclick="get_data(\'?c=list_rule\',\'central\');" class="btn btn-default btn-lg"><i class="fa fa-backward"></i> Back</a>
                                                <button type="submit" id=save_btn class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save</button>

                                            </div>

                                            <script type="text/javascript">
                        $(document).ready(function() {
                            $(\'#save_btn\').click(function(e) {                                 
                                var dataS = $(\'form#update_rules\').serialize();
                                e.preventDefault();
                                $.ajax({
                                    type: "POST",
                                    url: \'index.php?c=save_rule_db\',
                                    data: dataS,
                                    success: function(data) {
                                          $(\'#show_result\').html(data) ;
                                      
                                          setTimeout("get_data(\'?c=list_rule\',\'central\')", 1000);                                       
                                    }
                                })
                                return false;
                            });
                        });

                        function sleep(milliseconds) {
                          var start = new Date().getTime();
                          for (var i = 0; i < 1e7; i++) {
                            if ((new Date().getTime() - start) > milliseconds){
                              break;
                            }
                          }
                        }

                    </script>


                    ';
    $str .= '
            </form>
            <div>
        </div>
    </div>';
    return $str;
}
/**
 * [show_dashboard show welcome panel for stats]
 * @return [type] [description]
 */
function show_dashboard( ) {
    global $db_;
    $SQL = "SELECT *,inet_ntoa(que_ip_adr) as ip FROM block_queue group by que_ip_adr order by que_event_timestamp desc;";
    if ( !$result = $db_->query( $SQL ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $SQL )
    $count = $result->num_rows;
    $str .= ' <div class="row">
                       
                         
                       
                       <div class="col-xs-12 col-sm-7">
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                Active Alert Blocked (' . $count . ')  - Time: ' . date( "H:i:s", time() ) . '
                               </div>
                               <div class="panel-body">
                                   <table class="table table-condensed table-hover">
                                    <thead>
                                        <tr>
                                                <th> <i class="fa fa-clock-o"></i> Time</th><th>IP Block</th> <th>Rule</th><th>SID</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody>   ';
    while ( $row = $result->fetch_assoc() ) {
        $str .= '<tr><td> ' . format_fecha( $row[ 'que_event_timestamp' ] ) . '</td> <td>' . $row[ 'ip' ] . '</td><td >' . $row[ 'que_sig_name' ] . '</td><td><a target=_blank href=http://doc.emergingthreats.net/' . $row[ 'que_sig_sid' ] . '>' . $row[ 'que_sig_sid' ] . '</a></small></td><td> &nbsp;</td></tr>';
    }
    $str .= '
                                    </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                       
                       <div class="col-xs-12 col-sm-5">
                           <div class="panel panel-default">
                               <div class="panel-heading">

                                   Top Ten IP Attack
                               </div>

                                <div class="panel-body">
                                 ';
    $str .= show_table_top_ten( 1 );
    $str .= '
                               </div>
                              
                           </div>


                           
                           <div class="panel panel-default">
                               <div class="panel-heading">

                                   Top Ten Alert Rules
                               </div>

                                <div class="panel-body">
                                 ';
    $str .= show_table_top_ten( 2 );
    $str .= '
                               </div>
                              
                           </div>
                           
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                  MKE Solutions
                               </div>
                               <div class="panel-body">
                                   Designed by <a href="http://maxid.com.ar" target="_blank">Maximiliano Dobladez</a></a>
                               </div>
                           </div>
                       </div>
                       
                   </div>';
    return $str;
}
/**
 * [show_table_top_ten show tables with TOP TEN]
 * @param  string $type [description]
 * @return [type]       [description]
 */
function show_table_top_ten( $type = '1' ) {
    global $db_;
    if ( $type == "1" ) {
        $sql_query = "SELECT  inet_ntoa(que_ip_adr) as ip , count(*) as total FROM block_queue GROUP BY que_ip_adr
                    ORDER BY count(*) DESC LIMIT 10;";
        $str_th    = '  <th>Count</th> <th>IP Block</th>  <th>Country</th>';
    } else {
        $sql_query = "SELECT  que_sig_name,que_sig_sid ,count(*) as total FROM block_queue GROUP BY que_sig_name 
                    ORDER BY count(*) DESC LIMIT 10;";
        $str_th    = '  <th>Count</th> <th>Alert</th>  <th>Sid</th>';
    }
    if ( !$result = $db_->query( $sql_query ) ) {
        die( 'There was an error running the query [' . $db_->error . ']' );
    } //!$result = $db_->query( $sql_query )
    $count = $result->num_rows;
    $str .= '   <table class="table table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            ' . $str_th . '
                                        </tr>
                                    </thead>
                                    <tbody>   ';
    while ( $row = $result->fetch_assoc() ) {
        if ( $type == "1" ) {
            $str .= '<tr><td><small class="label label-default">' . $row[ 'total' ] . '</small></td>  <td ><small>' . $row[ 'ip' ] . '</small></td> <td ><small>' . geoip_country_name_by_name( $row[ 'ip' ] ) . '</small></td> </tr>';
        } else {
            $str .= '<tr><td><small class="label label-default">' . $row[ 'total' ] . '</small></td>  <td ><small>' . $row[ 'que_sig_name' ] . '</small></td> <td ><small><a target=_blank href=http://doc.emergingthreats.net/' . $row[ 'que_sig_sid' ] . '>' . $row[ 'que_sig_sid' ] . '</a></small></td> </tr>';
        }
    }
    $str .= '
                                    </tbody>
                                   </table>';
    return $str;
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
?>