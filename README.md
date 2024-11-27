
# PHP Media Manipulation Library

A powerful PHP library for adding image and text watermarks to images and videos. Built with the Intervention Image library and FFmpeg for seamless media processing.

## Features

- Add multiple image watermarks with custom positioning and dimensions.
- Add multiple text watermarks with flexible font, size, and color options.
- Add watermarks to PDFs.
- Convert TTF fonts to PHP array format.
- Compatible with both images and videos.
- Simple and extensible.

## Installation Dependencies

- composer require intervention/image 
- composer require php-ffmpeg/php-ffmpeg 
- composer require setasign/fpdf 
- composer require setasign/fpdi

- This will install the following packages:
    - Intervention Image: For image manipulation.
    - PHP-FFMpeg: For video manipulation.
    - FPDF and FPDI: For PDF-related functionalities (if any).
- Ensure FFmpeg is installed on your system. If not then Follow the steps in Installing FFmpeg on Windows.

# Installing FFmpeg on Windows

To use the video manipulation functionality of this library, you need to install FFmpeg on your Windows machine.

## Step-by-Step Installation

- Download FFmpeg:
    - Go to FFmpeg's download page (https://ffmpeg.org/download.html).
    - Scroll down and under Windows, click on "Windows builds from gyan.dev" or any other option that provides stable builds.
    - Download the latest Static build of FFmpeg for Windows.
- Extract FFmpeg:
    - After downloading the ZIP file, extract it to a folder on your computer, such as C:\ffmpeg.
- Add FFmpeg to System Path:
    - Right-click on "This PC" or "My Computer" and choose Properties.
    - Click on Advanced system settings.
    - Under System Properties, click on the Environment Variables button.
    - Under System variables, scroll down and select the Path variable, then click Edit.
    - Click New and add the path to the bin directory inside the extracted FFmpeg folder (e.g., C:\ffmpeg\bin).
    - Click OK to save the changes.
- Verify FFmpeg Installation:
    - Open the Command Prompt and type: ```ffmpeg -version```
    - If installed correctly, you should see FFmpeg's version and other related details.

# Usage

## Image Manipulation

### Adding Watermarks to an Image

```php

require_once 'Media_manipulation.php';

use App\Libraries\Media_manipulation;

$media = new Media_manipulation();

// Define watermark images
$watermark_images = [
    [
        'image' => 'assets/img/watermark1.png',
        'x_axis' => 100,
        'y_axis' => 200,
        'width' => 150,
        'height' => 0 // Auto-calculate height
    ],
    [
        'image' => 'assets/img/watermark2.png',
        'x_axis' => 400,
        'y_axis' => 50,
        'width' => 100,
        'height' => 100
    ],
];

// Define text watermarks
$text_watermarks = [
    [
        'text' => 'Sample Text Watermark',
        'x_axis' => 50,
        'y_axis' => 500,
        'font_size' => 24,
        'color' => '#FFFFFF'
    ],
];

$image_manipulation_response = $media->manipulateImage(
    'input_image.jpg',          // Original file path
    'output_image.jpg',         // Save path for manipulated image
    $watermark_images,          // Image watermarks
    $text_watermarks            // Text watermarks
);
```

## Video Manipulation

### Adding Watermarks to a Video

```php
require_once 'Media_manipulation.php';

use App\Libraries\Media_manipulation;

$media = new Media_manipulation();

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

$video_manipulation_response = $media->manipulateVideo(
    'input_video.mp4', 
    'output_video.mp4', 
    $watermark_images, 
    $text_watermarks, 
    $box_watermarks
);

```

## PDF Manipulation

### Adding Watermarks to a PDF

- This PDF library does not work with TTF font types, it needs font with ".php and .z" extensions. ttf to PHP conversion function and steps have been provided below.

```php
require_once 'Media_manipulation.php';

use App\Libraries\Media_manipulation;

$media = new Media_manipulation();

$custom_font_folder_path = 'assets/my_fonts';

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

 $text_watermarks = [
    'pages' => 1, // If not provided it will consider all pages.
    'data' => [
        [
            'text' => 'Sample Text',
            'font_size' => 23,
            'font_color' => '#000000',
            'x_axis' => 297 - 3,
            'y_axis' => 1080,
            'font' => 'your_font_path/arial.php'
        ],
        [
            'text' => 'example@email.com',
            'font_size' => 23,
            'font_color' => '#000000',
            'x_axis' => 297 - 3,
            'y_axis' => 1110,
            'font' => 'your_font_path/arial.php'
        ]
    ]
];

// Add watermark to a PDF
$pdf_manipulation_response = $media->manipulatePDF(
    'input_pdf.pdf',           // Original PDF path
    'output_pdf.pdf',          // Save path for manipulated PDF
    $watermark_images,            // Text watermarks for the PDF
    $text_watermarks,
    $custom_font_folder_path
);
```

### Font Conversion: `ttfToPHPZ`

The `ttfToPHPZ` function converts a TTF font file into a PHP array definition. This is useful for embedding fonts in projects such as PDF generation or custom rendering systems. The function also includes options for customization.

```php

require_once 'Media_manipulation.php';

use App\Libraries\Media_manipulation;

$media = new Media_manipulation();

$ttf_file = 'assets/fonts/Roboto-Regular.ttf';
$output_font_folder = 'assets/font_folder';
$enc_type = 'cp1252' // Not Mandatory

$converted_font_data = ttfToPHPZ($ttf_file,$output_font_folder, $enc_type);

```
#### Enc Types

Following is the list of Enc types:
    
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
