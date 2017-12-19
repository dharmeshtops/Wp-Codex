<?php
/*
Template name: INSERT CSV
*/
get_header();
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />  
           <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>   
           <div class="container" style="width:900px;">  
                <h2 align="center">Import Album CSV File</h2>  
                <form id="upload_csv" method="post" enctype="multipart/form-data">  
                     <div class="col-md-3">  
                     </div>  
                     <div class="col-md-4">  
                          <input type="file" name="employee_file" style="margin-top:15px;" />  
                     </div>  
                     <div class="col-md-5">  
                          <input type="submit" name="upload" id="upload" value="Import" style="margin-top:10px;" class="btn btn-info" />  
                     </div>  
                     <div style="clear:both"></div>  
                </form>  
                <br /><br /><br />  
                <div class="table-responsive" id="employee_table">  
                      
                </div>  
           </div>  
           
  <?php

if (!empty($_FILES["employee_file"]["name"]))
  {
  $output = '';
  $allowed_ext = array(
    "csv"
  );
  $albumExcel = $_FILES["employee_file"]["name"];
  $extension = pathinfo($albumExcel, PATHINFO_EXTENSION);
  if (in_array($extension, $allowed_ext))
    {
    $file_data = fopen($_FILES["employee_file"]["tmp_name"], 'r');
    fgetcsv($file_data);
    $output.= '  
                <table class="table table-bordered">  
                     <tr>  
                        <th width="5%">albumID</th>  
                        <th width="25%">albumName</th>  
                        <th width="10%">year</th>  
                        <th width="20%">subTitle</th>  
                        <th width="20%">banner</th>  
                        <th width="15%">Producer</th>
                        <th width="15%">Director</th>
                        <th width="15%">Actors_array</th>
                        <th width="15%">youtube_link</th>
                        <th width="15%">albumImage</th>
                    </tr>';
    while ($row = fgetcsv($file_data))
      {
      $albumID = $row[0];
      $year = $row[1];
      $songName = $row[2];
      $songRaag = $row[3];
      $female1 = $row[4];
      $male1 = $row[5];
      $female2 = $row[6];
      $male2 = $row[7];
      $female3 = $row[8];
      $male3 = $row[9];
      $chorus = $row[10];
      $lyricsname = $row[11];
      $Utubelink = $row[12];
      $audiofile = $row[13];
      $hindilyrics = $row[14];
      $englyrics = $row[15];
      $genre = $row[16];
      $typeofsog = $row[17];
      $chorus = ($chorus == 'Chorus') ? 1 : 0;
      $typeofsog = ($typeofsog == 'Title') ? 1 : 0;
      global $wpdb;
      $table_name = $wpdb->prefix . "songs_list";
      $songID = $wpdb->insert($table_name, array(
        'album_id' => $albumID,
        'song_name' => $songName,
        'raag' => $songRaag,
        'singer_female_1' => $female1,
        'singer_male_1' => $male1,
        'singer_female_2' => $female2,
        'singer_male_2' => $male2,
        'singer_female_3' => $female3,
        'singer_male_3' => $male3,
        'chorus' => $chorus,
        'lyricist' => $lyricsname,
        'video_url' => $Utubelink,
        'song_url' => $audiofile,
        'hindi_lyrics' => $hindilyrics,
        'eng_lyrics' => $englyrics,
        'genre' => $genre,
        'title_song' => $typeofsog,
      ));
      }
    }
    else
    {
    $response['flag'] = false;
    $response['message'] = 'Please select CSV file only!';
    }
  }
  else
  {
  $response['flag'] = false;
  $response['message'] = 'browse file not selected!';
  }

echo json_encode($response);
?>  

<?php
get_footer(); ?>

