## About backend project
- This helps to quickly handle file uploads both single and multiple file uploads are allowed. You can configure the mime types of files allowed to be uploaded to your server.

## Image upload functionality
- In your form you can do multiple images eg <input type="file" name="myimage[]" accept=".jpg,.png,.jpeg" multiple>
- Example of how to implement the upload is shown on the code snippet below.

```php
<?php
    use FileUploader\EmmaFileUploader;

    require "src/EmmaFileUploader.php";

    if (isset($_FILES['myimage'])) {
        $serverStoreDir = "../assets/property-uploads"; //The location on the server where the file will be store on your server. Ensure that this location exists else the file will not upload.

        $fileUploader = new EmmaFileUploader();

        //configure the uploader settings
        $fileUploader->setAllowedFileTypes([".jpg", "pdf", "png"]); // can only upload this type of files
        $fileUploader->setAllowedFileMaxSize(2.5); //eg 2.5mb maximum can upload with this code

            //uploads the file to the server $serverStoreDir. Ensure that this directory exists else the image will not upload. returns true if uploaded
        $fileUploader->uploadFile($serverStoreDir, "myimage");

        //get the system generated link to be stored on db for the file location on the server
        echo $fileUploader->getFilePath($serverStoreDir, "myimage");
    } else {
        echo "No file POSTED for upload. check that your input field enctype='multipart/form-data' or xhr Content-type: multipart/form-data";
    }
>
```
