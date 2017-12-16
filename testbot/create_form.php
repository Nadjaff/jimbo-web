<!--
	-we have our html form here where user information will be entered
	-we used the 'required' html5 property to prevent empty fields
-->
<form id='addUserForm' action='#' method='post' border='0'>
    <table>
        <tr>
            <td>Method</td>
            <td><select name="method" size="1" id="method">
                <option value="GET" selected="selected">GET</option>
                <option value="POST">POST</option>
                <option value="UPDATE">UPDATE</option>
                <option value="DELETE">DELETE</option>
            </select></td>
        </tr>
        <tr>
            <td>URL</td>
            <td><input name='url' type='text' value="<?php if ($_GET["type"] != NULL){ echo $_GET["type"];};?>" size="100" required="required" /></td>
        </tr>
        <tr>
            <td>Headers</td>
            <td><textarea name="headers" cols="100" rows="2">Authorization: 2665c944a206bc21b3b1664e0ebdceca</textarea></td>
        </tr>
        <tr>
            <td>Payload</td>
            <td><textarea name="payload" cols="100" rows="10"></textarea></td>
        </tr>
        <tr>
            <td>Uploaded File</td>
            <td><input type="file" name="uploadFile" /></td>
        </tr>
        <tr>
            <td></td>
            <td>                
                <input type='submit' value='Save' class='customBtn' />
            </td>
        </tr>
    </table>
</form>