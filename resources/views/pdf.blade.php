<!DOCTYPE html>
   <html>
   <head>
       <title>PDF</title>
   </head>
   <body>
       <h1>Selected Images</h1>
       <ul>
           @foreach($images as $image)
               <li>{{ $image }}</li>
           @endforeach
       </ul>
   </body>
   </html>