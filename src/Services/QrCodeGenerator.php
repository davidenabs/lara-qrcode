<?php

namespace LaraBoy\LaraQrcode\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\App;

class QrCodeGenerator
{
    /**
     * Generate a QR code with optional logo and customizations.
     *
     * @param string $data The data to encode in the QR code.
     * @param array $options Customization options for the QR code.
     * @return string The generated QR code as a PNG string or file.
     * @throws \InvalidArgumentException If the input is invalid.
     */
    public function generate(string $data, array $options = []): string
    {
        // Validate data type
        if (!is_string($data)) {
            throw new \InvalidArgumentException('The data provided must be a string.');
        }

        $config = App::make('config')->get('qrcode');

        $options = array_merge([
            'size' => $config['default_size'] ?? 300,
            'margin' => $config['default_margin'] ?? 1,
            'format' => $config['default_format'] ?? 'png',
            'logo' => null,
            'logo_size' => 100,
            'logo_align' => 'center',
            'logo_offset' => [0, 0],
            // 'foreground_color' => [0, 0, 0],
            // 'background_color' => [255, 255, 255],
            // 'error_correction_level' => 'H',
            'output' => 'base64', // Default output is base64
            'output_path' => null // Path for saving the image file (if required)
        ], $options);

        // Validate options
        $this->validateOptions($options);

        // Create the renderer style with custom size and margin
        $rendererStyle = new RendererStyle($options['size'], $options['margin']);

        // Create the image renderer with the chosen backend (Imagick)
        $renderer = new ImageRenderer(
            $rendererStyle,
            new ImagickImageBackEnd()
        );

        // Generate the base QR code
        $writer = new Writer($renderer);
        $qrImage = $writer->writeString($data);

        // Apply additional features (e.g., logo)
        if ($options['logo']) {
            $qrImage = $this->addLogo(
                $qrImage,
                $options['logo'],
                $options['logo_size'],
                $options['logo_align'],
                $options['logo_offset']
            );
        }

        // Output based on the selected format
        if ($options['output'] === 'base64') {
            return 'data:image/png;base64,' . base64_encode($qrImage);
        } elseif ($options['output'] === 'file' && $options['output_path']) {
            file_put_contents($options['output_path'], $qrImage);
            return $options['output_path']; // Return the file path
        } else {
            throw new \InvalidArgumentException('Invalid output option or missing file path.');
        }
    }

    /**
     * Validate the provided options array.
     *
     * @param array $options
     * @throws \InvalidArgumentException If any validation fails.
     */
    protected function validateOptions(array $options): void
    {
        if (!is_int($options['size']) || $options['size'] <= 0) {
            throw new \InvalidArgumentException('The "size" option must be a positive integer.');
        }

        if (!is_int($options['margin']) || $options['margin'] < 0) {
            throw new \InvalidArgumentException('The "margin" option must be a non-negative integer.');
        }

        if (isset($options['logo']) && !is_string($options['logo'])) {
            throw new \InvalidArgumentException('The "logo" option must be a string representing a file path or URL.');
        }

        if (!is_array($options['foreground_color']) || count($options['foreground_color']) !== 3) {
            throw new \InvalidArgumentException('The "foreground_color" option must be an array of three integers (RGB).');
        }

        if (!is_array($options['background_color']) || count($options['background_color']) !== 3) {
            throw new \InvalidArgumentException('The "background_color" option must be an array of three integers (RGB).');
        }

        foreach (['foreground_color', 'background_color'] as $colorKey) {
            foreach ($options[$colorKey] as $color) {
                if (!is_int($color) || $color < 0 || $color > 255) {
                    throw new \InvalidArgumentException("Each value in \"$colorKey\" must be an integer between 0 and 255.");
                }
            }
        }

        if ($options['output'] === 'file' && !is_string($options['output_path'])) {
            throw new \InvalidArgumentException('The "output_path" option must be a string representing a valid file path when "output" is set to "file".');
        }
    }

    protected function addLogo(string $qrImage, string $logoPath, int $logoSize, string $logoAlign, array $logoOffset): string
    {
        // Check if the logoPath is a URL
        if (filter_var($logoPath, FILTER_VALIDATE_URL)) {
            // Fetch the image content from the URL
            $logoContent = file_get_contents($logoPath);
            if ($logoContent === false) {
                throw new \Exception("Unable to fetch the logo from the URL.");
            }
            $logo = imagecreatefromstring($logoContent);
        } else {
            // Treat the logoPath as a local file
            if (!file_exists($logoPath)) {
                throw new \Exception("Logo file not found at path: $logoPath");
            }
            $logo = imagecreatefromstring(file_get_contents($logoPath));
        }

        $qr = imagecreatefromstring($qrImage);

        // Resize logo
        $resizedLogo = imagecreatetruecolor($logoSize, $logoSize);
        $white = imagecolorallocate($resizedLogo, 255, 255, 255);
        imagefill($resizedLogo, 0, 0, $white);
        imagecopyresampled($resizedLogo, $logo, 10, 10, 0, 0, $logoSize - 20, $logoSize - 20, imagesx($logo), imagesy($logo));

        // Merge logo onto QR code
        $qrWidth = imagesx($qr);
        $qrHeight = imagesy($qr);
        $xOffset = ($qrWidth - $logoSize) / 2; // Default to center alignment
        $yOffset = ($qrHeight - $logoSize) / 2;

        if ($logoAlign === 'left') {
            $xOffset = $logoOffset[0];
        } elseif ($logoAlign === 'right') {
            $xOffset = $qrWidth - $logoSize - $logoOffset[0];
        }

        $yOffset += $logoOffset[1];
        imagecopy($qr, $resizedLogo, $xOffset, $yOffset, 0, 0, $logoSize, $logoSize);

        ob_start();
        imagepng($qr);
        $output = ob_get_clean();

        return $output;
    }
}
