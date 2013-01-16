<?php
namespace ImageStorage\Image;

class Image
{
	private $_imageObject = null;
	private $_imageWidth = 0;
	private $_imageHeight = 0;

	/**
	 * @param ImageCropStruct $crop
	 *
	 * @return bool
	 */
	public function crop(ImageCropStruct $crop)
	{
		if ($this->_imageObject)
		{
			$newIm = imagecreatetruecolor($crop->width, $crop->height);
			imagecopyresized($newIm, $this->_imageObject, 0, 0, $crop->x, $crop->y, $crop->width, $crop->height, $crop->width, $crop->height);
			$this->_imageObject = $newIm;
			return true;
		}
		return false;
	}

	/**
	 * @param ImageResizeStruct $resize
	 *
	 * @return bool
	 */
	public function resize(ImageResizeStruct $resize)
	{
		$newHeight = 0;
		$newWidth = 0;

		if ($this->_imageObject)
		{
			if ($resize->width == 0 && $resize->height > 0)
			{
				$newHeight = $resize->height;
				$newWidth = $this->_imageWidth / ($this->_imageHeight / $resize->height);
			}
			elseif ($resize->height == 0 && $resize->width > 0)
			{
				$newHeight = $this->_imageHeight / ($this->_imageWidth / $resize->width);
				$newWidth = $resize->width;
			}
			elseif ($resize->width > 0 && $resize->height > 0)
			{
				if ($resize->scale == false)
				{
					$newHeight = $resize->height;
					$newWidth = $resize->width;
				}
				else
				{
					if ($resize->height > $resize->width)
					{
						$newHeight = $resize->height;
						$newWidth = $this->_imageWidth / ($this->_imageHeight / $resize->height);
					}
					else
					{
						$newHeight = $this->_imageHeight / ($this->_imageWidth / $resize->width);
						$newWidth = $resize->width;
					}
				}
			}

			if ($newHeight != 0 && $newWidth != 0)
			{
				$newIm = imagecreatetruecolor($newWidth, $newHeight);

				imagecopyresampled($newIm, $this->_imageObject, 0, 0, 0, 0, $newWidth, $newHeight, $this->_imageWidth, $this->_imageHeight);
				$this->_imageObject = $newIm;
				$this->_imageWidth = imagesx($newIm);
				$this->_imageHeight = imagesy($newIm);

				return true;
			}
		}

		return false;
	}

	/**
	 * @param        $filename
	 *
	 * @return bool
	 */
	public function watermark($filename)
	{
		if (file_exists($filename) && $this->_imageObject)
		{
			$image = $this->loadImage($filename);

			if ($image)
			{
				list($watermarkObject, $watermarkWidth, $watermarkHeight) = $image;
				imagecopyresampled($this->_imageObject, $watermarkObject, 0, 0, 0, 0, $watermarkWidth, $watermarkHeight, $watermarkWidth, $watermarkHeight);

				return true;
			}
		}

		return false;
	}

	/**
	 * @param $filename
	 *
	 * @return bool
	 */
	public function load($filename)
	{
		if (file_exists($filename))
		{
			$image = $this->loadImage($filename);

			if ($image != false)
			{
				list($this->_imageObject, $this->_imageWidth, $this->_imageHeight) = $image;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param $file
	 *
	 * @return array|bool
	 */
	private function loadImage($file)
	{
		$imageInformation = getimagesize($file);

		switch ($imageInformation[2])
		{
			case 1:
				// gif
				$im = imagecreatefromgif($file);
				break;

			case 2:
				// jpeg
				$im = imagecreatefromjpeg($file);
				break;

			case 3:
				// png
				$im = imagecreatefrompng($file);
				break;

			default:
				// other types
				return false;
				break;
		}

		return array(
			$im,
			$imageInformation[0],
			$imageInformation[1]
		);
	}

	/**
	 * @param        $filename
	 * @param string $format
	 * @param int    $quality
	 */
	public function save($filename, $format = 'jpg', $quality = 97)
	{
		if ($this->_imageObject)
		{
			switch (strtolower($format))
			{
				case "png":
					imagepng($this->_imageObject, $filename, $quality);
					break;

				default:
					imagejpeg($this->_imageObject, $filename, $quality);
					break;
			}
		}

		return;
	}
}

?>