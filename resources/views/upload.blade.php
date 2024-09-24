<!DOCTYPE html>
<html>
<head>
    <title>Upload Images</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .mborder-3 {
            padding-bottom: 35px !important; /* Corrected the placement of !important */
        }
        .show_img{
            width: 50px;
            height:50px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Upload Images and Create PDF</h2>
    <form id="uploadForm" enctype="multipart/form-data">
           @csrf <!-- Add this line to include the CSRF token -->
           <div class="form-group">
               <label for="images" id="imagesLabel">Select Images</label>
               <input type="file" class="form-control mborder-3" id="images" name="images[]" multiple>
           </div>
           <button type="button" class="btn btn-primary" id="uploadBtn">Upload Images</button>
           <button type="button" class="btn btn-success" id="createPdfBtn">Create PDF</button>
           <button type="button" class="btn btn-info" id="saveS3Btn">Save to S3</button>
    </form>
    <div id="uploadedImages" class="mt-3"></div> <!-- Add this div to display uploaded images with checkboxes -->
    <div id="pdfLink" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
    $('#images').change(function() {
        var files = $(this)[0].files;
        var fileNames = $.map(files, function(val) { return val.name; }).join(', ');
        $('#imagesLabel').text(fileNames || 'Select Images');
    });

    $('#uploadBtn').click(function() {
        var formData = new FormData($('#uploadForm')[0]);
        formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token to FormData
        $.ajax({
            url: '/upload',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                alert('Images uploaded successfully');
                $('#uploadForm').data('images', data.images);
                displayUploadedImages(data.images); // Call function to display images with checkboxes
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Response:', xhr.responseText); // Log the server response
                alert('An error occurred while uploading the images.');
            }
        });
    });

    function displayUploadedImages(images) {
        var html = '';
        images.forEach(function(image) {
            html += '<div class="form-check">';
            html += '<input class="form-check-input" type="checkbox" value="' + image + '" id="image_' + image + '">';
            html += '<img src="/images/'+ image+'" class="show_img" />';
            html += '<label class="form-check-label" for="image_' + image + '">' + image + '</label>';
            html += '</div>';
        });
        $('#uploadedImages').html(html);
    }

    $('#createPdfBtn').click(function() {
        var selectedImages = [];
        $('#uploadedImages input:checked').each(function() {
            selectedImages.push($(this).val());
        });
        console.log(selectedImages);
        $.ajax({
            url: '/create-pdf',
            type: 'POST',
            data: { 
                images: selectedImages,
                _token: '{{ csrf_token() }}' // Add CSRF token to the data
            },
            success: function(data) {
                alert('PDF created successfully');
                $('#uploadForm').data('pdf_path', data.pdf_path);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred while creating the PDF.');
            }
        });
    });

    $('#saveS3Btn').click(function() {
        var pdfPath = $('#uploadForm').data('pdf_path');
        $.ajax({
            url: '/save-s3',
            type: 'POST',
            data: { pdf_path: pdfPath },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add this line
            },
            success: function(data) {
                $('#pdfLink').html('<a href="'+data.s3_url+'" target="_blank">Open PDF</a>');
            }
        });
    });
</script>
</body>
</html>