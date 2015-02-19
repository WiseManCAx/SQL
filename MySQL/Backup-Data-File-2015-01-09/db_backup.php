<?php
require_once 'mysql_backup.php';

$restart = $_GET['restart'];
$file_name = "backup_details.csv";
$max_rows = 50000;
$large_tables = array("");

$file_contents = file('connection_details.csv');
$days_to_keep_backup = 10;

list($folder_name, $server_name) = explode(':', str_replace("\n", "", str_replace("\n", "", $file_contents[0])));
$folder_name = trim($folder_name);
$server_name = trim($server_name);

if (!is_dir($folder_name))
    mkdir($folder_name, 0777);
else
    chmod($folder_name, 0777);

/* $dir_list=filelist($folder_name,1,1);

  if(count($dir_list)>$days_to_keep_backup)
  {
  for($count=0;$count<count($dir_list);$count++)
  {
  if($dir_list[$count]=='')continue;
  rmdirtree($folder_name.$dir_list[$count]);
  }
  } */

chmod($folder_name, 0755);

$path = $folder_name;

/* * *********************************************************************************************************************************** */
list($user_name, $password, $database) = explode(':', str_replace("\n", "", str_replace("\n", "", $file_contents[1])));

$backup_obj = new BackupMySQL();

//----------------------- EDIT - REQUIRED SETUP VARIABLES -----------------------
$database = trim($database);

$backup_obj->server = $server_name;
$backup_obj->port = 3306;
$backup_obj->username = $user_name;
$backup_obj->password = $password;
$backup_obj->database = $database;

//-------------------- OPTIONAL PREFERENCE VARIABLES ---------------------
//Add DROP TABLE IF EXISTS queries before CREATE TABLE in backup file.
$backup_obj->drop_tables = true;

//Only structure of the tables will be backed up if true.
$backup_obj->struct_only = false;

//Include comments in backup file if true.
$backup_obj->comments = true;

$filename = $folder_name . date('d-m-Y') . '_' . $backup_obj->database . '.sql';

if ($restart != -1) {
    if ($restart == "1") {
        $table_details = $backup_obj->GetTables($database);

        $fp = fopen($file_name, 'w');

        for ($count = 0; $count < count($table_details); $count++) {
            fwrite($fp, $table_details[$count] . ":0\r\n");
        }
        fclose($fp);

        $fp = fopen($filename, 'w');
        fclose($fp);

        @unlink($filename . "gz");
    }

    $file_contents = file($file_name);

    $total_rows = 0;
    $table_count = count($file_contents);
    for ($count = 0; $count < $table_count; $count++) {
        list($table_name, $row_count, $start) = explode(':', str_replace("\r", "", str_replace("\n", "", $file_contents[$count])));

        if (in_array($table_name, $large_tables))
            $max_rows = 10000;
        else
            $max_rows = 50000;

        if ($start < $row_count || $row_count == 0) {
            if (($start + $max_rows) > $row_count)
                $end = ($row_count - $start);
            else
                $end = $max_rows;

            $str = $table_name . ":" . $row_count . ":" . ($start + $end) . "\r\n";
            $file_contents[$count] = $str;

            if (!$backup_obj->Execute($filename, $database, $table_name, $start, $end, $row_count)) {
                $output = $backup_obj->error;
                echo "Error backing up table " . $table_name . ". Details : " . $output;
            } else {
                $total_rows+=$end;
                if ($end != 0)
                    echo "Rows " . $start . "-" . ($start + $end - 1) . " of table " . $table_name . " were successfully backed up.<br/>";
                else
                    echo "Rows 0-0 of table " . $table_name . " were successfully backed up.<br/>";
                if ($total_rows >= $max_rows)
                    echo '<br/>';

                $fp = fopen($file_name, 'w');
                for ($counter = 0; $counter < count($file_contents); $counter++) {
                    fwrite($fp, $file_contents[$counter]);
                    if ($total_rows >= $max_rows)
                        echo $file_contents[$counter] . '<br/>';
                }
                fclose($fp);
            }

            if ($total_rows >= $max_rows)
                break;
        }
    }
}
else {
    if (gzcompressfile($filename, 1) == false) {
        echo '<br/>Gzipped file could not be created.';
        return false;
    } else {
        echo '<br/>Gzipped file was sucessfully created.';
        unlink($filename);
    }
}
/* * **************************************************************************
 * Remove a directory and its sub directory.
 * ************************************************************************** */

function rmdirtree($dirname) {
    if (is_dir($dirname)) {
        //Operate on dirs only
        $result = array();
        if (substr($dirname, -1) != '/') {
            $dirname.='/';
        }    //Append slash if necessary
        $handle = opendir($dirname);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {    //Ignore . and ..
                $path = $dirname . $file;
                if (is_dir($path)) {    //Recurse if subdir, Delete if file
                    $result = array_merge($result, rmdirtree($path));
                } else {
                    unlink($path);
                    $result[].=$path;
                }
            }
        }
        closedir($handle);
        rmdir($dirname);    //Remove dir
        $result[].=$dirname;
        return $result;    //Return array of deleted items
    } else {
        return false;    //Return false if attempting to operate on a file
    }
}

/* * **************************************************************************
 * Used to list all files in a directory.
 * ************************************************************************** */

function filelist($startdir = "./", $searchSubdirs = 1, $directoriesonly = 0, $maxlevel = "all", $level = 1) {
    //list the directory/file names that you want to ignore
    $ignoredDirectory[] = ".";
    $ignoredDirectory[] = "..";
    $ignoredDirectory[] = "_vti_cnf";
    global $directorylist;    //initialize global array
    if (is_dir($startdir)) {
        if ($dh = opendir($startdir)) {
            while (($file = readdir($dh)) !== false) {
                if (!(array_search($file, $ignoredDirectory) > -1)) {
                    if (filetype($startdir . $file) == "dir") {
                        //build your directory array however you choose;
                        //add other file details that you want.
                        $directorylist[$startdir . $file]['level'] = $level;
                        $directorylist[$startdir . $file]['dir'] = 1;
                        $directorylist[$startdir . $file]['name'] = $file;
                        $directorylist[$startdir . $file]['path'] = $startdir;
                        if ($searchSubdirs) {
                            if ((($maxlevel) == "all") or ( $maxlevel > $level)) {
                                filelist($startdir . $file . "/", $searchSubdirs, $directoriesonly, $maxlevel, $level + 1);
                            }
                        }
                    } else {
                        if (!$directoriesonly) {
                            //if you want to include files; build your file array 
                            //however you choose; add other file details that you want.
                            $directorylist[$startdir . $file]['level'] = $level;
                            $directorylist[$startdir . $file]['dir'] = 0;
                            $directorylist[$startdir . $file]['name'] = $file;
                            $directorylist[$startdir . $file]['path'] = $startdir;
                        }
                    }
                }
            }
            closedir($dh);
        }
    }
    return($directorylist);
}

/* * **************************************************************************
 * Used to gzip a file on disk.
 * ************************************************************************** */

function gzcompressfile($source, $level = false) {
    $dest = $source . '.gz';
    $mode = 'wb' . $level;
    $error = false;
    if ($fp_out = gzopen($dest, $mode)) {
        if ($fp_in = fopen($source, 'rb')) {
            while (!feof($fp_in))
                gzwrite($fp_out, fread($fp_in, 4096));
            fclose($fp_in);
        } else
            $error = true;
        gzclose($fp_out);
    } else
        $error = true;
    if ($error)
        return false;
    else
        return $dest;
}

/* * *********************************************************************************************************************************** */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
        <title>MySQL Backup Data</title>
    </head>
    <body>
        <?
        if($restart!=-1)echo $output;
        if($restart!=-1&&$count<$table_count)echo '<script>location.href="http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'?restart=0"</script>';	
        else if($restart!=-1) echo '<script>location.href="http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'?restart=-1"</script>';
        ?>
    </body>
</html>