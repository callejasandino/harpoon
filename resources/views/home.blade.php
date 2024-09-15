<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Full-width Form with Laravel Route</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #000; /* Jet black background */
      color: white;
      font-family: Arial, sans-serif;
    }

    .container {
      width: 80%;
      max-width: 600px;
      background-color: #333; /* Dark gray container background */
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    form {
      display: flex;
      width: 100%;
      align-items: center;
    }

    label {
      font-size: 18px;
      color: white;
      margin-right: 10px;
    }

    input[type="text"] {
      flex-grow: 1;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 16px;
      margin-right: 10px;
      transition: border 0.3s ease, box-shadow 0.3s ease;
      background-color: #222;
      color: white;
    }

    input[type="text"]:hover {
      border-color: #4a90e2;
      box-shadow: 0 0 8px rgba(74, 144, 226, 0.4);
    }

    input[type="text"]:focus {
      border-color: #4a90e2;
      box-shadow: 0 0 8px rgba(74, 144, 226, 0.6);
      outline: none;
    }

    button {
      padding: 12px 20px;
      border: none;
      border-radius: 4px;
      background-color: #4a90e2;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #357ABD;
    }
  </style>
</head>
<body>

  <div class="container">
    <form action="/check-url" method="POST">
      <!-- Add Laravel CSRF token for security -->
      @csrf
      <input type="text" id="url" name="url" placeholder="Enter URL">
      <button type="submit">Check</button>
    </form>
  </div>

</body>
</html>
