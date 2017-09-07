<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload audio file</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
  function confirm_prompt( text,url ) {
     if (confirm( text )) {
      window.location = url ;
    }
  }
</script>	
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<h3> Upload audio</h3>

<?php
include_once("image2video.php");

$project_id=$_POST['project_id'];
if( !$project_id ) $project_id=$_GET['project_id'];

$basedir=dirname(__FILE__);
$upload_dir="$basedir/uploads/$project_id";
$upload_url="./uploads/$project_id";
$bin_dir="$basedir/bin";

if( !file_exists( "$upload_dir/project.txt") ) {		
	echo "<h3> Error ! Cannot found project name </h3>";
	echo "Try again <a href=index.php> here </a>
		</body>
		</html>
	";	
	exit(0);
}

$errors= array();
$messages= array();
$effects_info= array();


$string = file_get_contents("$upload_dir/audio.txt");
$audio = json_decode($string, true);



$files_info=array();

	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo image2video::showMenu( $project_id );


if( ! $audio['name'] ) {
	$audio[1]['audio_enable']=1;
	$audio[1]['audio_rnd']=1;
}

	
$audio_enable_checked='';
$audio_rnd_checked='';

$audio_enable=$audio[1]['audio_enable'];
$audio_rnd=$audio[1]['audio_rnd'];

if( $audio_enable ) {
	$audio_enable_checked='checked';
}
if( $audio_rnd ) {
	$audio_rnd_checked='checked' ;
}







if( $_GET['del'] && $_GET['audio_id'] && $_GET['project_id'] ) {
	$audio_new=$audio;
	$audio_id=$_GET['audio_id'];
	unset( $audio[ $audio_id ] );
	$audio[1]['audio_rnd']= $audio_rnd;
	$audio[1]['audio_enable']=$audio_enable;
}
/*
echo '<pre>';
    print_r($audio);
echo '</pre>';
*/

$form="

	<table border=1>		
		<form action='upload_audio.php' method='post' multipart='' enctype='multipart/form-data'>		
			<input type='hidden' name='project_id' value='$project_id'>	
		";
foreach($audio as $k=>$val): 
        if( $audio[$k]['url'] ) {
			$audio_filename=$audio[$k]['name'];
			$audio_url=$audio[$k]['url'];
			$audio_selected_checked='';
			if( $audio[$k]['audio_selected'] ) {				
				$audio_selected_checked="checked";				
			}
			$form.="
			<tr>
				<td>Use this audio track</td>
				<td><input type='radio'  name='audio_selected' value=$k $audio_selected_checked > </td>
				<td><a href='$audio_url'> Audio $k </a></td>
				<td>Change audio file</td><td><input type='file' name='audioform[]' multiple> </td>
				<td>[ <a href='' onclick=\"confirm_prompt( 'Are you sure to remove this audio file?','?del=1&audio_id=$k&project_id=$project_id'); return false;\">Remove this audio</a> ] </td>
			</tr>
			";
		}
endforeach;	
		
$form.="			
			<tr>
				<td></td>
				<td></td>
				<td></a></td>
				<td>Add audio file</td><td><input type='file' name='audioform[]' multiple> </td>
				<td></td>
			</tr>
			<tr><td>Use randomize audio track</td><td><input type='checkbox'  name='audio_rnd' value='1' $audio_rnd_checked > </td><td></td><td></td></tr>
			<tr><td>Enable audio</td><td><input type='checkbox'  name='audio_enable' value='1' $audio_enable_checked > </td><td></td><td></td></tr>
			<tr>
				<td><input type='submit' name='save' id='save' value='Save file'></form></td>

			</tr>
	</table>
	";

if( $_POST['save'] || $_GET['del'] ) {	

$files_info=&$audio;
$audioform = $_FILES['audioform'];
if(!empty($audioform))
{

$audio_desc = image2video::reArrayFiles($audioform);

    $k=0;
    foreach($audio_desc as $val)
    {
		$k++;
		$files_info[$k]['audio_selected']=0;
		if( empty($val['name'])) 
		{
			continue;
		}
		$file_size=$val['size'];
		$file_type=$val['type'];
		$file_ext=strtolower(end(explode('.',$val['name'])));

		$file_sha1=sha1( $val['tmp_name'] );
		$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
		$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
		

		$expensions= array( "mp3","aac","wav","ac3" );


		if( in_array($file_ext,$expensions)=== false)
		{
			$errors[]="extension for file ".$val['name']." not allowed, please choose another file ( mp3, aac, wav, ac3 ).";
			continue;
		}
		if( !$file_size || $file_size> 8*1024*1024  )
		{
			$errors[]="File too big for uploading ( >8mb )";
			continue;
		}
		
		
		if ( move_uploaded_file( $val['tmp_name'], $file_name ) ) 
		{
			$messages[]="File ".$val['name']." saved to $file_name";			
		}	
		else 
		{
			$errors[]="Cannot save the file ".$val['name']." to $file_name";			
		}
		$files_info[$k]=array(
			'url'=>		$file_url,		
			'name'=>	$file_name,
			'ext'=>		$file_ext,
			'size'=>	$file_size,
			'type'=>	$file_type
		);

    }
	if( $_POST['save'] ) {	
		$files_info[1]['audio_enable']=$_POST['audio_enable'];	
		$files_info[1]['audio_rnd']=$_POST['audio_rnd'];	
		$audio_selected=$_POST['audio_selected'];
		if( $audio_selected ) {
			$files_info[ $audio_selected ]['audio_selected']=1;
		}
		$files_info[1]['audio_rnd']=$_POST['audio_rnd'];		
	}
} 



	$myfile = fopen("$upload_dir/audio.txt", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/audio.txt";
		}
	fwrite($myfile, json_encode ( $files_info ) );
	fclose($myfile);
	
	foreach($messages as $value)
		{
		echo "<font color=green>$value</font><br>";
		}			 			 
	foreach($errors as $value)
		{
		echo "<font color=red>$value</font><br>";
		}

		$form="
    <form action='edit_effect.php' method='post' multipart='' enctype='multipart/form-data'>	
	<table>		
        <tr><td></td><td><input type='submit' value='Next step'> </td></tr>
	</table>
	<input type='hidden' name='project_id' value='$project_id'>	
    </form>
		";
}



 echo $form;
?>



</body>
</html>