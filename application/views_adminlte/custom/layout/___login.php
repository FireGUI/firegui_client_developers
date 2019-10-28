<html>
<!-- 
RENAME THIS FILE FROM __login.php to login.php THAN REOPEN THE ACCESS PAGE.
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
-->
<style>
    /* Bordered form */
    form {
        /*border: 3px solid #f1f1f1;*/
    }

    /* Full-width inputs */
    input[type=text],
    input[type=password] {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }

    /* Set a style for all buttons */
    button {
        background-color: #4CAF50;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        cursor: pointer;
        width: 100%;
    }

    /* Add a hover effect for buttons */
    button:hover {
        opacity: 0.8;
    }

    /* Extra style for the cancel button (red) */
    .cancelbtn {
        width: auto;
        padding: 10px 18px;
        background-color: #f44336;
    }

    /* Center the avatar image inside this container */
    .imgcontainer {
        text-align: center;
        margin: 24px 0 12px 0;
    }

    /* Avatar image */
    img.avatar {
        width: 40%;
        border-radius: 50%;
    }

    /* Add padding to containers */
    .container {
        padding: 16px;
        width: 300px;
        margin: 0 auto;
        border: 3px solid #f1f1f1;
    }

    /* The "Forgot password" text */
    span.psw {
        float: right;
        padding-top: 16px;
    }

    /* Change styles for span and cancel button on extra small screens */
    @media screen and (max-width: 300px) {
        span.psw {
            display: block;
            float: none;
        }

        .cancelbtn {
            width: 100%;
        }
    }
</style>

<body>
    <form action="<?php echo base_url('access/login_start'); ?>" method="post">
        <input name="remember" value="1" type="hidden">
        <div class="container">
            <label for="uname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="users_users_email" required>

            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="users_users_password" required>

            <button type="submit">Login</button>
            <label>
                <b>Logout after:</b>
                <select name="timeout">
                    <!--<option value="1" class="form-control input-sm select2">1 minuto</option>-->
                    <option value="5" class="form-control input-sm select2">5 minutes</option>
                    <option value="10" class="form-control input-sm select2">10 minutes</option>
                    <option value="30" class="form-control input-sm select2">30 minutes</option>
                    <option value="60" class="form-control input-sm select2">1 hour</option>
                    <option value="120" class="form-control input-sm select2">2 hours</option>
                    <option value="240" class="form-control input-sm select2" selected="selected">4 hours</option>
                    <option value="480" class="form-control input-sm select2">8 hours</option>
                    <option value="720" class="form-control input-sm select2">12 hours</option>
                    <option value="1440" class="form-control input-sm select2">1 day</option>
                    <option value="10080" class="form-control input-sm select2">7 days</option>
                    <option value="43200" class="form-control input-sm select2">1 month</option>
                    <option value="518400" class="form-control input-sm select2">Never</option>
                </select>
            </label>
        </div>

        <div class="container" style="background-color:#f1f1f1">
            <button type="submit" class="cancelbtn">Cancel</button>
            <span class="psw">Forgot <a href="<?php echo base_url("access/recovery"); ?>">password?</a></span>
        </div>
    </form>
</body>

</html>