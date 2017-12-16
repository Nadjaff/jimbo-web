<?php
try {
    include 'libs/db_connect.php';
    
    //prepare query
    $query = "select 
                id, method, url, headers, payload, returnval, ignorekeys, attachment
            from 
                tests" . $_GET["s"] . " 
            where 
                id = ? 
            limit 0,1";
            
    $stmt = $con->prepare( $query );

    //this is the first question mark
    $stmt->bindParam(1, $_REQUEST['test_id']);

    //execute our query
    if($stmt->execute()){
    
        //store retrieved row to a variable
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //values to fill up our form
        $id = $row['id'];
        $method = $row['method'];
        $url = $row['url'];
        $headers = $row['headers'];
        $payload = $row['payload'];
        $returnval = $row['returnval'];
        $ignorekeys = $row['ignorekeys'];
        $attachment = $row['attachment'];
        
    }else{
        echo "Unable to read record.";
    }
}

//to handle error
catch(PDOException $exception){
    echo "Error: " . $exception->getMessage();
}
?>
<h1>Edit Test #<?php echo $id?></h1>
<!--we have our html form here where new user information will be entered-->
<form id='updateUserForm' action='#' method='post' border='0'>
    <table>
        <tr>
            <td>Method</td>
            <td><select name="method" size="1" id="method" required>
                <option value="GET" <?php if($method == 'GET'){echo("selected");}?>>GET</option>
                <option value="POST" <?php if($method == 'POST'){echo("selected");}?>>POST</option>
                <option value="UPDATE" <?php if($method == 'UPDATE'){echo("selected");}?>>UPDATE</option>
                <option value="DELETE" <?php if($method == 'DELETE'){echo("selected");}?>>DELETE</option>
            </select></td>
        </tr>
        <tr>
            <td>URL</td>
            <td><input type='text' name='url' value='<?php echo $url;  ?>' required /></td>
        </tr>
        <tr>
            <td>Headers</td>
            <td><input type='text' name='headers'  value='<?php echo $headers;  ?>' /></td>
        </tr>
        <tr>
            <td>Payload</td>
            <td><input type='text' name='payload' value='<?php echo $payload;  ?>'/></td>
        </tr>
        <tr>
            <td>Uploaded File</td>
            <td><input type="text" name="attachment" value='<?php echo $attachment; ?>' /> OR <input type="file" name="uploadFile" /></td>
        </tr>
        <tr>
            <td>Return</td>
            <td><input type='text' name='returnval' value='<?php echo $returnval;  ?>'/></td>
        <tr>
        <tr>
            <td>Ignore Keys</td>
            <td><input type='text' name='ignorekeys' value='<?php echo $ignorekeys;  ?>'/></td>
        <tr>
        <tr>
            <td></td>
            <td>
                <!-- so that we could identify what record is to be updated -->
                <input type='hidden' name='id' value='<?php echo $id ?>' />
                <input type='submit' value='Update' class='customBtn' />
                <input type='button' value='Cancel' class='customBtn cancel' />
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    
</script>