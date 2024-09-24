<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';

//How It Works
//Compressing and Splitting:

//The script gzips a directory into a .tar.gz archive. 
//
//If the archive size exceeds 20 MB, the file is split using the split command.
// ********** Gmail accepts actually 25MB attachments
//The split files are named using the pattern: 
// directory_backup_<timestamp>.tar.gz_part_aa, 
// directory_backup_<timestamp>.tar.gz_part_ab, 
// etc.
//
// Emailing Split Files:
//
// Each split part is sent in a separate email via PHPMailer, 
// with one attachment per email.
//
// Leveraging Gmail for Virus Scanning:
//
// Gmail automatically scans attachments for viruses, malware, and rootkits. 
// If a file is infected, Gmail will discard the attachment.
// As a result, the recipient gets a virus-free backup, 
// ensuring that no infected files are retained from the server.
//
// In simple words the last email contain the last not infected situation


// Function to gzip and split the directory
function gzipAndSplitDirectory($sourceDir) {
    $timestamp = time();  // Unix timestamp
    $archiveName = "directory_backup_$timestamp.tar.gz";  // Name with timestamp
    $splitSize = 20 * 1024 * 1024;  // 20 MB in bytes

    $sourceDir = escapeshellarg($sourceDir);  // Escape input for safety
    $archiveName = escapeshellarg($archiveName);

    // Create the tar.gz file
    $command = "tar -czf $archiveName -C $sourceDir .";
    shell_exec($command);

    // Check if the tar.gz file exists
    if (!file_exists($archiveName)) {
        return false;
    }

    // Get file size
    $fileSize = filesize($archiveName);

    // If file size exceeds the split size, split it into smaller chunks
    if ($fileSize > $splitSize) {
        $splitCommand = "split -b {$splitSize} $archiveName {$archiveName}_part_";
        shell_exec($splitCommand);

        // Return array of split files
        return glob("{$archiveName}_part_*");
    }

    // Return the original tar.gz file if splitting wasn't necessary
    return [$archiveName];
}

// Function to send an email with a single attachment
function sendEmail($filePath, $partNumber) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                      // Send using SMTP
        $mail->Host = 'smtp.example.com';                     // Set the SMTP server to send through
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'your_email@example.com';           // SMTP username
        $mail->Password = 'your_password';                    // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // Enable SSL encryption
        $mail->Port = 465;                                    // TCP port to connect to

        // Recipients
        $mail->setFrom('from_email@example.com', 'Your Name');
        $mail->addAddress('recipient@example.com', 'Recipient Name'); // Add a recipient

        // Attach the single file part
        $mail->addAttachment($filePath);  // Attach the file

        // Email content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "Backup Part $partNumber";
        $mail->Body    = "Attached is part $partNumber of your backup file.";
        $mail->AltBody = "Attached is part $partNumber of your backup file.";

        // Send the email
        $mail->send();
        echo "Part $partNumber has been sent.\n";
    } catch (Exception $e) {
        echo "Part $partNumber could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
    }
}

// Define source directory
$directoryToGzip = 'path/to/your/directory';

// Gzip and split the directory
$filesToAttach = gzipAndSplitDirectory($directoryToGzip);
if ($filesToAttach) {
    echo "Directory gzipped and split successfully!\n";

    // Send each part in a separate email
    foreach ($filesToAttach as $index => $file) {
        sendEmail($file, $index + 1);  // Send email with each part as an attachment
    }
} else {
    echo "Failed to gzip the directory.\n";
}
?>
