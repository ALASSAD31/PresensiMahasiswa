<?php
// Load OpenCV library
if (!extension_loaded('opencv')) {
    die('OpenCV extension not loaded!');
}
// Load face detection and recognition models
$faceCascade = new
CvCascadeClassifier('haarcascade_frontalface_default.xml');
$faceRecognizer = CvEigenFaceRecognizer::create();
// Load face image from camera
$camera = new CvCapture(0);
$frame = $camera->queryFrame();
$gray = $frame->convertColor(CV_BGR2GRAY);
$faces = $faceCascade->detectMultiScale($gray);
if (empty($faces)) {
    die('No face detected!');
}
$face = $gray->getImageROI($faces[0]);
$face->resize(new CvSize(100, 100));
// Recognize face using face recognition model
$faceRecognizer->read('model.yml');
$predictedLabel = $faceRecognizer->predict($face);
// Get NIM, nama, and program studi from folder name
$folderName = basename(dirname($predictedLabel));
list($nim, $nama) = explode('_', $folderName, 2);
$programStudi = substr($nim, 0, 2) == '10' ? 'Sistem Informasi' : 'Informatika';
// Display result
echo '<h1>Presensi Mahasiswa</h1>';
echo '<p>NIM: ' . $nim . '</p>';
echo '<p>Nama: ' . $nama . '</p>';
echo '<p>Program Studi: ' . $programStudi . '</p>';
echo '<img src="data:image/png;base64,' . base64_encode($face-
>encode('.png')) . '">';
// Save result to database
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'presensi_mahasiswa';
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
// Prepare SQL statement
$stmt = $conn->prepare('INSERT INTO presensi (nim, nama, program_studi)
VALUES (?, ?, ?)');
$stmt->bind_param('sss', $nim, $nama, $programStudi);
// Execute SQL statement
$stmt->execute();
// Close connection
$conn->close();
