# Server-Security-Backup-Solution-Using-PHP-PHPMailer-and-Gmail-Virus-Scanning
By Claudio Klemp, in cooperation with ChatGPT

https://chatgpt.com/share/66f27b24-d0ac-8002-9986-076d1ae6b1c2

Introduction
This post explains a secure backup solution that automates the process of gzipping server directories, splitting large backups into smaller parts, and emailing them via PHPMailer to a Gmail account. Gmail's built-in virus scanning discards infected parts, providing a virus-free backup. This method is especially valuable after a server has been cleaned from malware or other threats.

Problem
When dealing with a compromised server, it's crucial to back up files without carrying over potential infections. Virus scanning tools can sometimes miss certain threats, so an additional layer of security is beneficial.

Solution Overview
This solution automates the process of:

Compressing a directory.
Splitting the archive into parts if its size exceeds 20 MB.
Sending each part as a separate email attachment using PHPMailer.
Leveraging Gmail’s virus scanning to discard infected attachments.
Key Benefits
Automated Backup Process: Gzips directories and handles large files by splitting them into manageable parts.
Gmail Virus Scanning: Any infected parts are automatically discarded, ensuring a clean backup.
No User Input for File Naming: The backup filenames include a Unix timestamp, ensuring unique names without manual input.
