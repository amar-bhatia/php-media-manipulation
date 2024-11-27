<?php 
	namespace App\Libraries;

	use Intervention\Image\ImageManager;
	use Intervention\Image\Drivers\Gd\Driver;
	use Intervention\Image\Typography\FontFactory;
	use FFMpeg\FFMpeg;
	use FFMpeg\Coordinate\Dimension;
	use FFMpeg\Format\Video\X264;
	use setasign\Fpdi\Fpdi;
	use FPDF;

	class Media_manipulation{

		public function manipulateImage($original_file_path, $manipulated_file_save_path, $watermark_images = [], $text_watermarks = []){

			/* ======================================================
						Watermark Images Example Array
				
				$watermark_images = [
		            [
		                'image' => 'assets/img/image1.jpg',
		                'x_axis' => 557,
		                'y_axis' => 1104,
		                'width' => 200,
		                'height' => 0
		            ],
		            [
		                'image' => 'assets/img/image2.jpg',
		                'x_axis' => 24,
		                'y_axis' => 1076,
		                'width' => 181,
		                'height' => 0
		            ]
		        ];

		    ========================================================= */

			/* ======================================================
						Watermark Text Example Array

		        $text_watermarks = [
		            [
		                'text' => 'Sample Text',
		                'font_size' => 23,
		                'font_color' => '#000000',
		                'x_axis' => 297 - 3,
		                'y_axis' => 1080,
		                'font' => 'your_font_path/arial.ttf'
		            ],
		            [
		                'text' => 'example@email.com',
		                'font_size' => 23,
		                'font_color' => '#000000',
		                'x_axis' => 297 - 3,
		                'y_axis' => 1110,
		                'font' => 'your_font_path/arial.ttf'
		            ]
		        ];

			========================================================= */

			// create image manager with desired driver
        	$manager = new ImageManager(new Driver());

        	// read image from file system
        	$image = $manager->read($original_file_path);

        	for ($i=0; $i <count($watermark_images) ; $i++) { 
	            if(!empty($watermark_images[$i]['width']) && empty($watermark_images[$i]['height']) ){
	                $watermark_image_resized = $manager->read($watermark_images[$i]['image'])->scale($watermark_images[$i]['width']);
	            }

	            if(!empty($watermark_images[$i]['height']) && empty($watermark_images[$i]['width']) ){
	                $watermark_image_resized = $manager->read($watermark_images[$i]['image'])->scale(height: $watermark_images[$i]['height']);
	            }

	            if(!empty($watermark_images[$i]['width']) && !empty($watermark_images[$i]['height']) ){
	                $watermark_image_resized = $manager->read($watermark_images[$i]['image'])->resize($watermark_images[$i]['width'], $watermark_images[$i]['height']);
	            }

	            $watermark_image_x_axis = (!empty($watermark_images[$i]['x_axis']))?$watermark_images[$i]['x_axis']:0;
	            $watermark_image_y_axis = (!empty($watermark_images[$i]['y_axis']))?$watermark_images[$i]['y_axis']:0;

	            // insert watermark cobrand image
	            $image->place($watermark_image_resized,'',$watermark_image_x_axis,$watermark_image_y_axis);
	        }

	        if(!empty($text_watermarks)){
	            for ($i=0; $i <count($text_watermarks) ; $i++) {
					$font_data = [
	                	'font_family' => $text_watermarks[$i]['font'],
	                    'font_size' => $text_watermarks[$i]['font_size'],
	                    'font_color' => $text_watermarks[$i]['font_color']
	                ];

	                $text_x_axis = $text_watermarks[$i]['x_axis'] - 3;
	                $text_y_axis = $text_watermarks[$i]['y_axis'] + ($font_data['font_size'] + 1);

	                // insert watermark text
	                $image->text($text_watermarks[$i]['text'], $text_x_axis, $text_y_axis, function (FontFactory $font) use ($font_data) {
	                    $font->filename($font_data['font_family']);
	                    $font->size($font_data['font_size']);
	                    $font->color($font_data['font_color']);
	                });
	            }
	        }

	        if(!is_dir(dirname($manipulated_file_save_path))){
	        	mkdir(dirname($manipulated_file_save_path),0755);
	        }

	        // save modified image in new format 
	        $image->save($manipulated_file_save_path, 100,'jpg');

	        return [
	        	'status' => true,
	        	'msg' => 'File manipulated successfully!'
	        ];
		}

		public function manipulateVideo($original_file_path, $manipulated_file_save_path, $watermark_images = [], $text_watermarks = [], $box_watermarks = []){

			/* ======================================================
						Watermark Images Example Array
				
				$watermark_images = [
		            [
		                'path' => 'assets/img/image1.png',
		                'x_axis' => 403,
		                'y_axis' => 350
		            ],
		            [
		                'path' => 'assets/img/image2.jpg',
		                'x_axis' => 716,
		                'y_axis' => 299,
		                'width' => 150,
		                'height' => 150
		            ]
		        ];
			
			========================================================= */

			
			/* ======================================================
						Watermark Text Example Array

				$text_watermarks = [
		            [
		                'text' => 'Sample Text',
		                'x_axis' => 363,
		                'y_axis' => 300,
		                'font_size' => 40,
		                'font_color' => "#000000",
		                'font_file' => "your_font_path/arial.ttf"
		            ],
		            [
		                'text' => 'New Sample Text',
		                'x_axis' => 363,
		                'y_axis' => 350,
		                'font_size' => 40,
		                'font_color' => "#000000",
		                'font_file' => "your_font_path/arial.ttf",
		                'box' => [
		                    'width' => 315,
		                    'height' => 69,
		                    'x_padding' => 16,
		                    'y_padding' => 8,
		                    'bg_color' => '#FFFFFF',
		                    'box_fill_type' => 'fill'
		                ]
		            ]
		        ];

			========================================================= */


			/* ======================================================
						Watermark Box Example Array

				$box_watermarks = [
		            [
		                'width' => 135,
		                'height' => 22,
		                'x_axis' => 0,
		                'y_axis' => 0,
		                'bg_color' => '#FFFFFF',
		                'box_fill_type' => 'fill' // This is for border thickness, e.g: 1 or 2 or any numeric value will draw a border. but if given "fill" it will make it a box of that color
		            ],
		            [
		                'width' => 135,
		                'height' => 22,
		                'x_axis' => 0,
		                'y_axis' => 50,
		                'bg_color' => '#FFFFFF',
		                'box_fill_type' => 'fill'
		            ]
		        ];

		    ========================================================= */

			$ffmpeg = FFMpeg::create();
        	$video = $ffmpeg->open($original_file_path);


        	try {
	            // Get video filters
	            $video_filters = $video->filters();

	            // Initialize filter command
	            $filter_command = "";

	            if(!empty($watermark_images)){
	                $watermark_images_count = count($watermark_images);
	                $image_list = '';
	                $overlays_list = '';

	                for ($i=0; $i <$watermark_images_count ; $i++) { 
	                    $img_path = $watermark_images[$i]['image'];
	                    $x_axis = (!empty($watermark_images[$i]['x_axis']))?$watermark_images[$i]['x_axis']:0;
	                    $y_axis = (!empty($watermark_images[$i]['y_axis']))?$watermark_images[$i]['y_axis']:0;
	                    $image_width = (!empty($watermark_images[$i]['width']))?$watermark_images[$i]['width']:0;
	                    $image_height = (!empty($watermark_images[$i]['height']))?$watermark_images[$i]['height']:0;

	                    $wm_inc = 'wm';
	                    $wm_inc .= $i + 1;

	                    $wm = ($i == 0)?'wm':'wm'.$i;
	                    $tmp = ($i == 0)?'tmp':'tmp'.$i;

	                    $image_scale = "-1:-1";

	                    if(!empty($image_width)){
	                    	$image_scale = "$image_width:-1";
	                    }

	                    if(!empty($image_height)){
	                    	$image_scale = "-1:$image_height";
	                    }

	                    if($watermark_images_count == 1){
	                        $filter_command .= "movie='$img_path', scale=$image_scale [$wm]; ";
	                        $filter_command .= "[0:v][$wm] overlay=$x_axis:$y_axis";
	                    }else{
	                        $image_list .= "movie='$img_path', scale=$image_scale [$wm]; "; 
	                        $overlays_list .= "overlay=$x_axis:$y_axis ";

	                        if($i < ($watermark_images_count - 1)){
	                            $overlays_list .= "[$tmp]; [$tmp][$wm_inc] ";
	                        }
	                    }
	                }

	                $wm_last_count = $watermark_images_count - 1;

	                if($watermark_images_count > 1){
	                    $filter_command .= $image_list.' [in][wm] '. $overlays_list;
	                }
	            }

	            if(!empty($box_watermarks)){

	            	$box_filter_command = '';
	            	$total_box_watermarks_count = count($box_watermarks);

	            	for($i=0;$i<$total_box_watermarks_count;$i++){
	            		$tmp_increment = $i + 2;
	            		$box_width = $box_watermarks[$i]['width'];
	            		$box_height = $box_watermarks[$i]['height'];
	            		$box_x_axis = $box_watermarks[$i]['x_axis'];
	            		$box_y_axis = $box_watermarks[$i]['y_axis'];
	            		$box_bg_color = $box_watermarks[$i]['bg_color'];
	            		$box_fill_type = $box_watermarks[$i]['box_fill_type'];

	            		if($i == 0){
	            			$image_watermark_filter_command = (!empty($tmp))?"[$tmp];[$tmp] ":"";
	            		}else{
	            			$image_watermark_filter_command = '';
	            		}


	            		$box_filter_command .= $image_watermark_filter_command." drawbox=x=$box_x_axis:y=$box_y_axis:w=$box_width:h=$box_height:color=$box_bg_color:t=$box_fill_type";



	            		if( ($total_box_watermarks_count > 1  && $i < ($total_box_watermarks_count - 1)) || !empty($text_watermarks) ){
	            			$box_filter_command .= " [tmp".$tmp_increment."];[tmp".$tmp_increment."]";
	            		}
	            	}
	            }

	            if(!empty($text_watermarks)){

	            	if(!empty($box_filter_command)){
	            		$filter_command .= $box_filter_command;
	            	}

	                $text_watermarks_count = count($text_watermarks);
	                $text_watermark_box_count = 0;
	                $text_filter_command = "";

	                for ($i=0; $i < $text_watermarks_count; $i++) {
	                    
	                    $tmp_text_increment = (empty($box_filter_command))?$i + 2:$tmp_increment + 1;
	                    $text = $text_watermarks[$i]['text'];
	                    $font_color = $text_watermarks[$i]['font_color'];
	                    $font_file = $text_watermarks[$i]['font'];
	                    $font_size = $text_watermarks[$i]['font_size'];
	                    $text_x_axis = (!empty($text_watermarks[$i]['x_axis']))?$text_watermarks[$i]['x_axis']:0;
	                    $text_y_axis = (!empty($text_watermarks[$i]['y_axis']))?$text_watermarks[$i]['y_axis']:0;

	                    if(!empty($text_watermarks[$i]['box'])){

	                    	$text_watermark_box_count += 1;

	                        $box_width = $text_watermarks[$i]['box']['width'];  // Adjust width for horizontal padding                    
	                        $box_height = $text_watermarks[$i]['box']['height'];
	                        $box_x_padding = $text_watermarks[$i]['box']['x_padding'];   // Horizontal padding for the box
	                        $box_y_padding = $text_watermarks[$i]['box']['y_padding'];   // Vertical padding for the box
	                        $box_x_axis = $text_x_axis - $box_x_padding;
	                        $box_y_axis = $text_y_axis - $box_y_padding;
	                        $box_bg_color = $text_watermarks[$i]['box']['bg_color'];
	                        $box_fill_type = $text_watermarks[$i]['box']['box_fill_type'];

	                        if($i == 0 && $text_watermarks_count > 1 && empty($box_filter_command)){
	                            $image_watermark_filter_command = (!empty($image_watermark_filter_command))?$image_watermark_filter_command:"";
	                        }else{
	                         	if(!empty($watermark_images) && empty($box_watermarks)){
	                         		$image_watermark_filter_command = (!empty($tmp))?"[$tmp];[$tmp]":"";
	                         	}else{
	                         		$image_watermark_filter_command = "";
	                         	}
	                        }


	                        $text_box_filter_command = "$image_watermark_filter_command drawbox=x=$box_x_axis:y=$box_y_axis:w=$box_width:h=$box_height:color=$box_bg_color:t=$box_fill_type";
	                    }else{
	                       $text_box_filter_command = '';
	                    }

	                    $text_x_padding = (!empty($text_box_filter_command))?$text_x_axis + $box_x_padding:$text_x_axis;

	                    // Adjust text y position to account for top padding
	                    $text_y_padding = (!empty($text_box_filter_command))?$text_y_axis + $box_y_padding:$text_y_axis;  // Position text inside the box with padding 

	                    if(!empty($text_box_filter_command) || !empty($watermark_images) ){

	                        $text_filter_command .= $text_box_filter_command;

	                        if(empty($box_filter_command) || (!empty($box_filter_command) && empty($watermark_images)) || (!empty($watermark_images) && empty($box_filter_command)) || !empty($text_box_filter_command) ){
	                        	$text_filter_command .= " [tmp".$tmp_text_increment."];[tmp".$tmp_text_increment."]";
	                        }

	                    }

	                    $text_filter_command .= " drawtext=text='$text':fontfile=$font_file:fontcolor=$font_color:fontsize=$font_size:x=$text_x_padding:y=$text_y_padding";

	                    if( ($text_watermarks_count == 1 && $i < ($text_watermarks_count - 1) && empty($watermark_images)) || ($text_watermarks_count > 0 && $i < ($text_watermarks_count - 1) ) ){
	                        $text_filter_command .= " [tmp".$tmp_text_increment."];[tmp".$tmp_text_increment."]";
	                    }
	                }
	            }else{
	            	if(!empty($box_filter_command) && empty($watermark_images)){
	            		$filter_command = $box_filter_command;
	            	}else{
	            		if(!empty($box_filter_command)){
	            			$filter_command .= $box_filter_command;
	            		}
	            	}
	            }

	            $text_watermarks_count = !(empty($text_watermarks_count))?$text_watermarks_count:0;

	            if($text_watermarks_count > 1 && empty($box_filter_command)){

	            	$text_filter_tmp_command = (!empty($tmp))?"[$tmp];[$tmp] ":"";

	            	if(empty($watermark_images)){
	            		$text_filter_tmp_command .= "[in]";
	            	}
	            	
	            	$filter_command .= $text_filter_tmp_command.$text_filter_command;
	            }else{
	            	if(!empty($text_filter_command)){
	            		$filter_command .= $text_filter_command;
	            	}
	            }

	            if(!empty($box_filter_command) && empty($watermark_images)){
	            	$filter_command = "[in]".$filter_command;
	            }


	            if(!empty($watermark_images) || ( !empty($text_watermark_box_count) && ($text_watermark_box_count > 1 || $text_watermarks_count > 1) ) || !empty($box_filter_command) || $text_watermarks_count > 1 ){
	                $filter_command .= " [out]"; // Last command ends with [out]
	            }
	           

	            // Finalize the filter command
	            if (!empty($filter_command)) {
	                $video_filters->custom($filter_command);
	            }


	            if(!is_dir(dirname($manipulated_file_save_path))){
	            	mkdir(dirname($manipulated_file_save_path),0755);
	            }

	            // Save the video with watermark
	            $video->save(new X264(), $manipulated_file_save_path);

	            return [
	            	'status' => true,
	            	'msg' => 'File manipulated successfully!'
	            ];

        	} catch (Exception $e) {
		        return [
		        	'status' => false,
		        	'msg' => $e->getMessage()
		        ];
		    }
		}

		public function manipulatePDF($original_file_path, $manipulated_file_save_path, $watermark_images = [], $text_watermarks = [], $custom_font_folder_path = ''){

			/* ======================================================
						Watermark Images Example Array
				
				$watermark_images = [
		            [
		                'image' => 'assets/img/image1.jpg',
		                'x_axis' => 557,
		                'y_axis' => 1104,
		                'width' => 200,
		                'height' => 0,
		                'pages' => 1 // If not provided it will consider all pages.
		            ],
		            [
		                'image' => 'assets/img/image2.jpg',
		                'x_axis' => 24,
		                'y_axis' => 1076,
		                'width' => 181,
		                'height' => 0
		            ]
		        ];

		    ========================================================= */

			/* ======================================================
						Watermark Text Example Array

		        $text_watermarks = [
		        	'pages' => 1, // If not provided it will consider all pages.
		            'data' => [
			            [
			                'text' => 'Sample Text',
			                'font_size' => 23,
			                'font_color' => '#000000',
			                'x_axis' => 297 - 3,
			                'y_axis' => 1080,
			                'font' => 'your_font_path/arial.ttf'
			            ],
			            [
			                'text' => 'example@email.com',
			                'font_size' => 23,
			                'font_color' => '#000000',
			                'x_axis' => 297 - 3,
			                'y_axis' => 1110,
			                'font' => 'your_font_path/arial.ttf'
			            ]
		            ]
		        ];

			========================================================= */
			
			if(!empty($text_watermarks) && !empty($custom_font_folder_path)){

	        	/* If you are sending custom fonts in $text_watermarks, do provide your custom fold folder path too. or else library won't be able to read the font, default font directory is: "\vendor\setasign\fpdf\font\" */

            	define('FPDF_FONTPATH', dirname($custom_font_folder_path));
            }

			// Create new FPDI instance
	        $pdf = new FPDI('P','in');

	        $page_count = $pdf->setSourceFile($original_file_path);

	        // Loop through all the pages of the existing PDF
	        for ($page_no = 1; $page_no <= $page_count; $page_no++) {
	            // Import a page
	            $template_id = $pdf->importPage($page_no);
	            $size = $pdf->getTemplateSize($template_id);

	            // Create a new page with the same size as the original
	            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

	            // Use the imported page as the background
	            $pdf->useTemplate($template_id);
				
				
				// Insert the logo at the desired position (e.g., center of the page)
				// Adjust the position and size as needed

				if(!empty($watermark_images)){
					/* All the below watermark calculations are in inches */

					for ($i=0; $i <count($watermark_images) ; $i++) {

						$pages_to_watermark_images = range(1, $watermark_images[$i]['pages'] ?? $page_count);

						if (in_array($page_no, $pages_to_watermark_images)) {
							$image_path = $watermark_images[$i]['image'];
							$width = (!empty($watermark_images[$i]['width']))?$watermark_images[$i]['width']:0; // Adjust 50 to your logo width
							$height = (!empty($watermark_images[$i]['height']))?$watermark_images[$i]['height']:0; // Adjust 50 to your logo height
							$x_axis = (!empty($watermark_images[$i]['x_axis']))?$watermark_images[$i]['x_axis']:0;  // Adjust horizontal position from left
							$y_axis = (!empty($watermark_images[$i]['y_axis']))?$watermark_images[$i]['y_axis']:0; // Adjust vertical position

							$pdf->Image($image_path, $x_axis, $y_axis,$width, $height); // 50 = width of the logo (adjust as necessary)
						}
					}
				}

					if(!empty($text_watermarks)){

						$pages_to_watermark_texts = range(1, $text_watermarks['pages'] ?? $page_count);

						if (in_array($page_no, $pages_to_watermark_texts)) {
							for ($i=0; $i <count($text_watermarks['data']) ; $i++) {
								$watermark_text = $text_watermarks['data'][$i]['text'];
								$font_family = $text_watermarks['data'][$i]['font_family'];
								$font_size = $text_watermarks['data'][$i]['font_size']; // in pt
								$x_axis = (!empty($text_watermarks['data'][$i]['x_axis']))?$text_watermarks['data'][$i]['x_axis']:0; // in inches
								$y_axis = (!empty($text_watermarks['data'][$i]['x_axis']))?$text_watermarks['data'][$i]['y_axis']:0; // in inches

								if(!empty($custom_font_folder_path)){
									$pdf->AddFont($font_family, '', basename($custom_font_folder_path,PATHINFO_FILENAME)); // Add the font
								}

								$pdf->SetFont($font_family, '', $font_size);   // Set the font

								if(!empty($text_watermarks['data'][$i]['font_color'])){
									list($r, $g, $b) = $this->hexToRgb($text_watermarks['data'][$i]['font_color']);
									$pdf->SetTextColor($r, $g, $b);
								}

								$pdf->Text($x_axis, $y_axis, $watermark_text);
							}
						}

					}
				
	        }

	        if(!is_dir(dirname($manipulated_file_save_path))){
            	mkdir(dirname($manipulated_file_save_path),0755);
            }

	        // Save the output PDF
	        $pdf->Output($manipulated_file_save_path, 'F');
	        
	        return [
            	'status' => true,
            	'msg' => 'File manipulated successfully!'
            ];
		}

		/* If you do not have font with ".php" and ".z" extension use function ttfToPHPZ() to convert ttf file to ".php" and ".z" etension, remember you will need both the files for the custom font to work */

		public function ttfToPHPZ($font_path, $custom_font_folder_path, $enc = '',){

			/* ======================================================
				Following are the enc types that are supported:
					
					1. cp1250 (Central Europe)
					2. cp1251 (Cyrillic)
					3. cp1252 (Western Europe) - Default
					4. cp1253 (Greek)
					5. cp1254 (Turkish)
					6. cp1255 (Hebrew)
					7. cp1257 (Baltic)
					8. cp1258 (Vietnamese)
					9. cp874 (Thai)
					10. ISO-8859-1 (Western Europe)
					11. ISO-8859-2 (Central Europe)
					12. ISO-8859-4 (Baltic)
					13. ISO-8859-5 (Cyrillic)
					14. ISO-8859-7 (Greek)
					15. ISO-8859-9 (Turkish)
					16. ISO-8859-11 (Thai)
					17. ISO-8859-15 (Western Europe)
					18. ISO-8859-16 (Central Europe)
					19. KOI8-R (Russian)
					20. KOI8-U (Ukrainian)

			====================================================== */

			// Include MakeFont
	        require_once FCPATH . 'vendor/setasign/fpdf/makefont/makefont.php';


	        $font = \FontLib\Font::load($font_path);
			$font->parse();  // for getFontWeight() to work this call must be done first!
			$font_script_name = $font->getFontPostscriptName();
			$font->close();

	        // Convert TTF to PHP font
	        try {
	        	$enc = (!empty($enc))?$enc:'cp1252';

	        	// Start output buffering to suppress echoed content
        		ob_start();

	        	makeFont(FCPATH . $font_path, $enc);

	        	// End output buffering and discard the output
        		ob_end_clean();

        		$font_name = pathinfo($font_path, PATHINFO_FILENAME);
        		rename(FCPATH.'/'.$font_name.'.php', $custom_font_folder_path.$font_name.'.php');
        		rename(FCPATH.'/'.$font_name.'.z', $custom_font_folder_path.$font_name.'.z');

	        	return [
	        		'status' => true,
	        		'font_og_name' => $font_script_name,
	        		'font_path' => [
	        			$custom_font_folder_path.'/'.$font_name.'.php',
	        			$custom_font_folder_path.'/'.$font_name.'.z',
	        		]
	        	];

	        } catch (Exception $e) {
	        	return [
	        		'status' => false,
	        		'msg' => $e->getMessage()
	        	];
	        }
		}

		public function hexToRgb($hex) {
		    $hex = ltrim($hex, '#');
		    $r = hexdec(substr($hex, 0, 2));
		    $g = hexdec(substr($hex, 2, 2));
		    $b = hexdec(substr($hex, 4, 2));

		    return [$r, $g, $b];
		}

	}
