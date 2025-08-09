<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= !empty($title) ? "$title - Sports" : 'Sports' ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/color_style.css">
  <style>
    html,
    body {
      height: 100%;
      font-family: "Noto Sans TC", sans-serif;
    }

    a {
      text-decoration: none !important
    }

    .sidebar li:hover {
      background: #e9e9e9;
    }

    .text-dark-blue.sidebar-link.active {
      color: #007bff !important;
      font-weight: 500;
    }

    .text-secondary.sidebar-link.active {
      color: #0041cf !important;
      font-weight: 500;
    }

    .search-field b {
      color: #dc3545;
    }

    .table td,
    .table th {
      min-width: 100px;
    }

    .sm-th,
    .sm-td {
      min-width: 67px !important;
    }

    .table-active {
      --bs-table-color-state: #004085 !important;
      --bs-table-bg-state: rgb(233, 244, 255) !important;
    }

    .text-orange {
      color: #fd7e14 !important;
    }

    .bg-orange {
      background-color: #fd7e14 !important;
    }

    .bg-orange-subtle {
      background-color: #ffe5d0 !important;
    }

    .table-orange {
      --bs-table-color: #000;
      --bs-table-bg: #ffe5d0;
      --bs-table-border-color: rgb(204, 180, 166);
      color: var(--bs-table-color);
      border-color: var(--bs-table-border-color);
    }
  </style>
</head>

<body>
  <div class="d-flex" style="min-height: 100vh;">