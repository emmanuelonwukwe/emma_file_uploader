<?php
    namespace FileUploader;

    use Exception;

    final class EmmaFileUploader {

        //The default allowed file types that can be uploaded
        private $defaultAllowedFileTypes = array("png", "jpeg", "jpg", "gif", 'pdf');

        //The allowed file type the user sets to override the default allowed types
        private $allowedFileTypes = null;

        private $defaultAllowedFileSize = 1.5*1000000; //1.5 mb
        //user allowed file size
        private $allowedFileSize = null;


        /**
         * This function generates and sets the random number used for each image referencing
         * @param array<int, string> $setAllowedFileTypes - The allowed file types eg ["jpg", "pdf"] 
         * @return void
         */
        public function setAllowedFileTypes(array $setAllowedFileTypes) {
            if (!is_array($setAllowedFileTypes)) {
                throw new Exception("Allowed file types must be an array of the file extensions.");
            }

            $setAllowedFileTypes = preg_replace("/[.]+/", '', $setAllowedFileTypes);

            return count($setAllowedFileTypes > 0) ? $this->allowedFileTypes = $setAllowedFileTypes : null;
        }

        /**
         * This helps to set the maximum size of the file the user can upload for each file.
         * @param float $setAllowedFileSize - The file size the user is allowed to upload
         * @throws \App\Http\Exceptions\Exception;
         * @return void
         */
        public function setAllowedFileMaxSize(float $setAllowedFileSize) {
            if (!is_float($setAllowedFileSize)) {
                throw new Exception("Your file size must be a number and less than 100MB");
            }

            return  $this->allowedFileSize = $setAllowedFileSize*1000000;
        }

        /**
         * This function generates and sets the random number used for each image referencing
         * @return void
         */
        private function setRandnum() : void {
            $this->randnum = mt_rand(10000, 99999);
        }

        /**
         * This function helps to check the image type and size
         * @param string $image_type - The type of image to be uploaded
         * @param string $image_size - The size of the image
         * @return void
         */
        private function checkFile($image_type, $image_size, $canBeNull){
            if (!$canBeNull) {
                if(empty($image_type) ){
                    throw new Exception("No file choosen");
                }
            }

            //image type check
            $allowedImages = $this->allowedFileTypes ?? $this->defaultAllowedFileTypes;
            if(!in_array($image_type, $allowedImages)){
                throw new Exception("Only ". implode(" | ", $allowedImages)." files are allowed to be uploaded");
            }

            //image size
            if($image_size > $accepted_max_size = ($this->allowedFileSize ?? $this->defaultAllowedFileSize)){
                throw new Exception("All files must be less than ". ($accepted_max_size/1000000). "MB");
            }
        }


        /**
         * This helps to make an upload in the system when called
         * if uploaded, it returns true else it returns false
         * @param string $toDir - The name of the directory where image will be uploaded to
         * @param string $inputTypeFileName - The name of the input field file
         * @param string $canBeNull - This allows for an input file field to be nullable
         * @return bool
         */
        public function uploadFile($toDir, $inputTypeFileName, $canBeNull = false) : bool {
            $this->setRandnum();

            //multiple file selection input
            if (is_array($_FILES[$inputTypeFileName]["name"])) {
                $len = count($_FILES[$inputTypeFileName]['name']);

                //check that the files are uploadable
                for ($i=0; $i < $len ; $i++) { 
                    $image_name = $this->randnum.'--'.$_FILES[$inputTypeFileName]['name'][$i];
                    $image_size= $_FILES[$inputTypeFileName]['size'][$i];
                    $image_tmp_name=$_FILES[$inputTypeFileName]['tmp_name'][$i];
                    $image_path="$toDir/$image_name";
                    $image_type=pathinfo($image_path, PATHINFO_EXTENSION);

                    $this->checkFile($image_type, $image_size, $canBeNull);
                }

                //Allow the files to upload on no error
                for ($i=0; $i < $len ; $i++) { 
                    $image_name = $this->randnum.'--'.$_FILES[$inputTypeFileName]['name'][$i];
                    $image_size= $_FILES[$inputTypeFileName]['size'][$i];
                    $image_tmp_name=$_FILES[$inputTypeFileName]['tmp_name'][$i];
                    $image_path="$toDir/$image_name";
                    $image_type=pathinfo($image_path, PATHINFO_EXTENSION);

                    move_uploaded_file($image_tmp_name, $image_path);
                }
    
            } else{

                $image_name = $this->randnum.'--'.$_FILES[$inputTypeFileName]['name'];
                $image_size= $_FILES[$inputTypeFileName]['size'];
                $image_tmp_name=$_FILES[$inputTypeFileName]['tmp_name'];
                $image_path="$toDir/$image_name";
                $image_type=pathinfo($image_path, PATHINFO_EXTENSION);

                $this->checkFile($image_type, $image_size, $canBeNull);

                move_uploaded_file($image_tmp_name, $image_path);
            }

            return true;
        }

        /**
         * This helps to get the image path of the image so it could be stored in database
         * @param string $toDir - The name of the directory where image will be uploaded to
         * @param string $inputTypeFileName - The name of the input field file
         * @param string $canBeNull - This allows for an input file field to be nullable
         * @return string
         */
        public function getFilePath($toDir, $inputTypeFileName) : string {
            //multiple file selection input
            if (is_array($_FILES[$inputTypeFileName]["name"])) {
                $len = count($_FILES[$inputTypeFileName]['name']);

                //check that the files are uploadable
                $image_paths = [];
                for ($i=0; $i < $len ; $i++) { 
                    $image_name = $this->randnum.'--'.$_FILES[$inputTypeFileName]['name'][$i];
                    $image_path="$toDir/$image_name";

                    $image_paths[] = $image_path;
                }

                return preg_replace("/\\\\/", '', json_encode($image_paths));
                //same as str_replace("\\", '', $image_paths); replace backslash added by sql real escape
            } else{
                $image_name = $this->randnum.'--'.$_FILES[$inputTypeFileName]['name'];
               
                $image_path="$toDir/$image_name";

                return preg_replace("/\\\\/", '', $image_path);
            }

        }
    }
    
?>