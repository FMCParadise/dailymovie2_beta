<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostFilesService
{
    private string $squareImagesDirectory;
    private string $fullImagesDirectory;
    private string $avatarImagesDirectory;

    private ParameterBagInterface $params;


    /**
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->squareImagesDirectory = $this->params->get('square_images_directory');
        $this->fullImagesDirectory = $this->params->get('full_images_directory');
        $this->avatarImagesDirectory = $this->params->get('avatar_images_directory');
    }

    /**
     * start process
     * @param UploadedFile $file
     * @return string
     * @throws FileException
     */
    public function processFile(UploadedFile $file , string $type = "POST" | "AVATAR"): string
    {
        if($type === "POST"){
            $this->checkImageSize($file, 1200, 600);

            $filename = $this->generateFilename($file);

            try {
                $this->generateAndSaveImage($file, $filename, $this->squareImagesDirectory,);

                $this->generateAndSaveImage($file, $filename, $this->fullImagesDirectory, 'FULL');

                return $filename;

            } catch (FileException $e) {
                throw new FileException("Une erreur est survenue lors du traitement de votre image");
            }
        }

        if($type === "AVATAR"){
            $this->checkImageSize($file, 200, 200);
            $filename = $this->generateFilename($file);
            try {
                $this->generateAndSaveImage($file, $filename, $this->avatarImagesDirectory, 'SQUARE');
                return $filename;
            } catch (FileException $e) {
                throw new FileException("Une erreur est survenue lors du traitement de votre image");
            }
        }
    }

    private function checkImageSize(UploadedFile $file, int $minWidth, int $minHeight): void
    {
        list($width, $height) = getimagesize($file->getRealPath());

        if ($width < $minWidth || $height < $minHeight) {
            throw new FileException('La taille de l\'image est trop petite. Elle doit faire au moins ' . $minWidth . 'x' . $minHeight . ' pixels.');
        }
    }

    /**
     * Genarete random file name with extension ".jpg"
     * @param UploadedFile $file
     * @return string
     */
    private function generateFilename(UploadedFile $file): string
    {
        return md5(uniqid()) . '.jpg';
    }

    /**
     * Generate and save image
     * @param UploadedFile $file
     * @param string $filename
     * @param string $directory
     * @param string $typeImg
     * @return void
     */
    private function generateAndSaveImage(UploadedFile $file, string $filename, string $directory, string $typeImg = 'SQUARE'): void
    {

        $infos = getimagesize($file);

        // create basic img
        $imgSource = match ($infos['mime']) {
            'image/png' => imagecreatefrompng($file),
            'image/jpeg' => imagecreatefromjpeg($file),
            default => throw new FileException("Une erreur est survenue lors du traitement de votre image"),
        };

        // Get width and height of the source image
        $largeur = $infos[0];
        $hauteur = $infos[1];

        // Calculate the size of the square
        $newWidth = ($typeImg === 'SQUARE') ? min($largeur, $hauteur) : $largeur;
        $newHeight = ($typeImg === 'SQUARE') ? $newWidth : intval(9 * $largeur / 16);
        $emptyImage = imagecreatetruecolor($newWidth, $newHeight);

        // calculate position
        $offsetX = ($typeImg === 'SQUARE') ? intval(($largeur - $hauteur) / 2) : 0;
        $offsetY = ($typeImg === 'SQUARE') ? 0 : intval(($hauteur - $newHeight) / 2);;

        // Crop and copy the image to the square image
        imagecopy(
            $emptyImage,
            $imgSource,
            0,
            0,
            $offsetX,
            $offsetY,
            $newWidth,
            $newHeight,
        );

        //save file
        imagejpeg($emptyImage, $directory . '/' . $filename);

        //update chmod
        chmod( $directory . '/' . $filename, 0644);

        imagedestroy($imgSource);

        imagedestroy($emptyImage);

    }
}
