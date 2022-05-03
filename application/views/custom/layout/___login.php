<html>
<!-- 
RENAME THIS FILE FROM __login.php to login.php THAN REOPEN THE ACCESS PAGE.
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
-->

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

        <div class="container">
            <button type="submit" class="cancelbtn">Cancel</button>
            <span class="psw">Forgot <a href="<?php echo base_url("access/recovery"); ?>">password?</a></span>
        </div>
    </form>
</body>

</html>