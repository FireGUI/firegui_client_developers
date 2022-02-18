<?php

function resize_image($path, $thumb_width, $thumb_height, $config)
{
   $size  = getimagesize($path); // [width, height, type index]
   $width = $size[0];
   $height = $size[1];

   $mode = $config['mode'] ?? IMG_BILINEAR_FIXED;

   list($new_width, $new_height) = calculateDestinationWidthHeight($size, $thumb_width, $thumb_height, $mode);
   if ($type = getImageType($path)) {

      $load        = 'imagecreatefrom' . $type;
      //$save        = 'image'           . $type;
      $image       = $load($path);
      if (false) {
         //IMG_NEAREST_NEIGHBOUR, IMG_BILINEAR_FIXED, IMG_BICUBIC, IMG_BICUBIC_FIXED
         $resized = imagescale($image, $new_width, $new_height, $mode);
      } else {
         $resized     = imagecreatetruecolor($thumb_width, $thumb_height);
         $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
         imagesavealpha($resized, true);
         imagefill($resized, 0, 0, $transparent);
         imagecopyresampled($resized, $image, 0 - ($new_width - $thumb_width) / 2, 0 - ($new_height - $thumb_height) / 2, 0, 0, $new_width, $new_height, $size['0'], $size['1']);
         imagedestroy($image);
      }

      return ['image' => $resized, 'type' => $type];
   }
}

function getImageType($path)
{
   $size  = getimagesize($path);
   $types = ACCEPTED_EXTENSIONS;
   if (array_key_exists($size['2'], $types)) {
      $type = $types[$size['2']];
   } else {
      $type = false;
   }

   return $type;
}

function calculateDestinationWidthHeight($size, $thumb_width, $thumb_height, $mode)
{
   $width = $size[0];
   $height = $size[1];

   $original_aspect = $width / $height;
   $thumb_aspect = $thumb_width / $thumb_height;

   if ($original_aspect >= $thumb_aspect) {
      // If image is wider than thumbnail (in aspect ratio sense)
      $new_height = $thumb_height;
      $new_width = $width / ($height / $thumb_height);
   } else {
      // If the thumbnail is wider than the image
      $new_width = $thumb_width;
      $new_height = $height / ($width / $thumb_width);
   }




   return [$new_width, $new_height];
}

function exifExtract($image)
{
   $exif = exif_read_data($image, 0, true);

   //d($exif);

   foreach ($exif as $key => $section) {
      foreach ($section as $name => $val) {
         echo "$key.$name: $val<br />\n";
      }
   }
}
