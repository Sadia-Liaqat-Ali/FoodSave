
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Online Food Waste Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:#001BB7;
            --accent:#FF8040;
            --bg:#F5F1DC;
            --text-dark:#001B44;
        }
        body{background: var(--bg); color: var(--text-dark); font-family: 'Roboto', sans-serif;}
        .sidebar{background: var(--primary); min-height:100vh;}
        .content-header{padding:30px 0; text-align:center;}
        .content-header h1{font-weight:700;}
        .section{padding:60px 15px; max-width:900px; margin:auto;}
        .section h2{color: var(--primary); margin-bottom:20px;}
        .section p, .section form label{line-height:1.8; font-size:1.1rem;}
        .form-control{border-radius:8px;}
        .btn-primary{background: var(--primary); border:none;}
    </style>
</head>
<body>

<div class="container-fluid">


        <!-- Main content -->
        <div class="col-md-10 p-4">

            <div class="content-header">
                <h1>Contact Us</h1>
                <p>Have questions or need assistance? Reach out to us!</p>
            </div>

            <div class="section card-box">
                <h2>Contact Information</h2>
                <p><i class="fas fa-envelope me-2"></i> Email: support@foodwasteplatform.com</p>
                <p><i class="fas fa-phone me-2"></i> Phone: +92 300 1234567</p>
                <p><i class="fas fa-map-marker-alt me-2"></i> Address: 123 Main St, Lahore, Pakistan</p>
            </div>

            <div class="section card-box">
                <h2>Send Us a Message</h2>
                <form action="contact_submit.php" method="post">
                    <div class="mb-3">
                        <label for="name">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($user_name); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email">Your Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>
