<style>
    input,
    select,
    textarea {
        text-transform: uppercase;
    }

    table {
        /* display: block !important; */
        overflow-x: auto !important;
        /* white-space: nowrap !important; */

    }

    table tbody {
        /* display: table !important; */
        width: auto !important;
    }

    .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.9em;
        font-family: sans-serif;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
        background-color: #3490dc;
        /* background-color: #009879; */
        color: #ffffff;
        text-align: left;
    }

    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #3490dc;
    }

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #3490dc;
    }


    @import 'https://fonts.googleapis.com/css?family=Open+Sans:600,700';

    * {
        font-family: 'Open Sans', sans-serif;
    }

    .rwd-table {
        margin: auto;
        min-width: 300px;
        max-width: 100%;
        border-collapse: collapse;
    }

    .rwd-table tr:first-child {
        border-top: none;
        /* background: #428bca; */
        color: #fff;
    }

    .rwd-table tr {
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        background-color: #f5f9fc;
    }

    .rwd-table tr:nth-child(odd):not(:first-child) {
        background-color: #ebf3f9;
    }

    .rwd-table th {
        display: none;
    }

    .rwd-table td {
        display: block;
    }

    .rwd-table td:first-child {
        margin-top: .5em;
    }

    .rwd-table td:last-child {
        margin-bottom: .5em;
    }

    .rwd-table td:before {
        content: attr(data-th) ": ";
        font-weight: bold;
        width: 120px;
        display: inline-block;
        color: #000;
    }

    .rwd-table th,
    .rwd-table td {
        text-align: left;
    }

    .rwd-table {
        color: #333;
        border-radius: .4em;
        overflow: hidden;
    }

    .rwd-table tr {
        border-color: #bfbfbf;
    }

    .rwd-table th,
    .rwd-table td {
        padding: .5em 1em;
    }

    @media screen and (max-width: 601px) {
        .rwd-table tr:nth-child(2) {
            border-top: none;
        }
    }

    @media screen and (min-width: 600px) {
        .rwd-table tr:hover:not(:first-child) {
            background-color: #d8e7f3;
        }

        .rwd-table td:before {
            display: none;
        }

        .rwd-table th,
        .rwd-table td {
            display: table-cell;
            padding: .25em .5em;
        }

        .rwd-table th:first-child,
        .rwd-table td:first-child {
            padding-left: 0;
        }

        .rwd-table th:last-child,
        .rwd-table td:last-child {
            padding-right: 0;
        }

        .rwd-table th,
        .rwd-table td {
            padding: 1em !important;
        }
    }


    /* THE END OF THE IMPORTANT STUFF */

    /* Basic Styling */
    body {
        background: #4B79A1;
        background: -webkit-linear-gradient(to left, #4B79A1, #283E51);
        background: linear-gradient(to left, #4B79A1, #283E51);
    }

    h1 {
        text-align: center;
        font-size: 2.4em;
        color: #f2f2f2;
    }

    .container {
        display: block;
        text-align: center;
    }

    h3 {
        display: inline-block;
        position: relative;
        text-align: center;
        font-size: 1.5em;
        color: #cecece;
    }

    h3:before {
        content: "\25C0";
        position: absolute;
        left: -50px;
        -webkit-animation: leftRight 2s linear infinite;
        animation: leftRight 2s linear infinite;
    }

    h3:after {
        content: "\25b6";
        position: absolute;
        right: -50px;
        -webkit-animation: leftRight 2s linear infinite reverse;
        animation: leftRight 2s linear infinite reverse;
    }

    @-webkit-keyframes leftRight {
        0% {
            -webkit-transform: translateX(0)
        }

        25% {
            -webkit-transform: translateX(-10px)
        }

        75% {
            -webkit-transform: translateX(10px)
        }

        100% {
            -webkit-transform: translateX(0)
        }
    }

    @keyframes leftRight {
        0% {
            transform: translateX(0)
        }

        25% {
            transform: translateX(-10px)
        }

        75% {
            transform: translateX(10px)
        }

        100% {
            transform: translateX(0)
        }
    }


    .delete {
        color: red;
        background-color: white;
        border-radius: 7%;
        padding: 3%;
    }

    table td::before {
            content: attr(data-label) !important;
            float: left !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            white-space:nowrap;
        }


    .swal2-content span {
        display: block;
        text-align: left;
    }

    .badge-left {
        display: flex;
        align-items: center;
    }

    .badge-left span {
        margin-right: 0.5rem;
    }


</style>
