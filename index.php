<?php

/**
 * Author: Ivan Grishankov
 *
 * Date of implementation: 13.02.2023 9:01
 *
 * Database utility: phpMyAdmin
 */

//Connecting to Database
try {
    $db = new PDO('mysql:host=localhost;dbname=people', getenv('DB_USER'), getenv('DB_PASS'));
} catch (PDOException $e) {
    echo "Can't connect: " . $e->getMessage();
    exit();
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Adding Class files
require_once 'Mapper.php';
require_once 'GenCollection.php';

//Showing and working with main form to add person
show_form();

if (isset($_POST['add_person'])) {
    list($errors, $input) = validate_form();
    if($errors) {
        show_form($errors);
    } else {
        process_form($input);
    }
}

//Showing and working with form to delete person
delete_form();

if (isset($_POST['delete'])) {
    $id = sanitize_string($_POST['delete_id']);
    $deleting_person = new Mapper((int) $id);
    $deleting_person->delete();
}

//Showing and working with form to transform person
transform_form();

if (isset($_POST['transform'])) {
    $id = sanitize_string($_POST['transform_id']);
    $transforming_person = Mapper::transform((int) $id);

    if (!is_null($transforming_person)) {
        print_r($transforming_person);
        echo '<br>Transformation of this Person has been done successfully';
    }
}

//Showing and working with form to collect people
get_collection_form();

if (isset($_POST['collection'])) {
    $id         = sanitize_string($_POST['collect_id']);
    $expression = sanitize_string($_POST['expression']);
    try {
        $people = new GenCollection((int) $id, $expression);

        foreach ($people->genObjs as $man)
        {
            print_r($man);
            echo '<br>';
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

//Showing and working with form to delete people
delete_collection_form();

if (isset($_POST['delete_collection'])) {
    $id         = sanitize_string($_POST['delete_collection_id']);
    $expression = sanitize_string($_POST['delete_expression']);
    try {
        $people = new GenCollection((int) $id, $expression);

        foreach ($people->genObjs as $man)
        {
            print_r($man);
            echo '<br>';
        }

        $people->deleteCollection();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function show_form($errors = array()): void
{
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error;
        }
    }

    echo <<<_FORM_
        <form method="POST" action="index.php">
            <fieldset>
                <legend>Adding New Person</legend>
        
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" required>
            
                <label for="birthday">Birthday:</label>
                <input type="date" id="birthday" name="birthday" required>
            
                <div>
                    Gender:
                    <input type="radio" id="contactChoice1"
                           name="sex" value="1" checked>
                    <label for="contactChoice1">Male</label>
            
                    <input type="radio" id="contactChoice2"
                           name="sex" value="0">
                    <label for="contactChoice2">Female</label>
                </div>
            
                <label for="birth_city">Birth City</label>
                <input type="text" id="birth_city" name="birth_city" required>
            
                <input type="submit" id="add_person" name="add_person" value="Add Person">
            </fieldset>
        </form>
_FORM_;

}

function validate_form(): array
{
    $input = $errors = array();

    foreach ($_POST as $post_key => $post_value) {
        $input[$post_key] = sanitize_string($post_value);
    }

    if ( ! preg_match('#^[a-zA-Z]*$#', $input['name']) &&
        ! preg_match('#^[a-zA-Z]*$#', $input['surname'])
    ) {
        $errors[] = 'Please enter only letters in "name" and "surname" fields';
    }
    return array($errors, $input);
}

function sanitize_string(?string $var): ?string
{
    $var = strip_tags($var);
    $var = htmlentities($var);

    return stripslashes($var);
}

function process_form($input): void
{
    $person = new Mapper(-1, $input['name'], $input['surname'], $input['birthday'],
        $input['sex'], $input['birth_city']);
    print_r($person);
}

function delete_form(): void
{
    echo <<<_DELETE_
        <form method="POST" action="index.php">
            <fieldset>
                <legend>Delete Person</legend>
        
                <label for="delete_id">Person ID to Delete</label>
                <input type="number" id="delete_id" name="delete_id" min="1" required>
            
                <input type="submit" id="delete" name="delete" value="Delete Person">
            </fieldset>
        </form>
_DELETE_;

}

function transform_form(): void
{
    echo <<<_TRANSFORM_
        <form method="POST" action="index.php">
            <fieldset>
                <legend>Transform Person</legend>
        
                <label for="transform_id">Person ID to Transform</label>
                <input type="number" id="transform_id" name="transform_id" min="1" required>
            
                <input type="submit" id="transform" name="transform" value="Transform Person">
            </fieldset>
        </form>
_TRANSFORM_;

}

function get_collection_form(): void
{
    echo <<<_COLLECTION_
        <form method="POST" action="index.php">
            <fieldset>
                <legend>Get People Collection</legend>
        
                <label for="collect_id">Person ID to Collect People</label>
                <input type="number" id="collect_id" name="collect_id" min="1" required>
            
                <div>
                    Expression:
                    <input type="radio" id="Choice1"
                           name="expression" value="more" checked>
                    <label for="Choice1">More</label>
            
                    <input type="radio" id="Choice2"
                           name="expression" value="less">
                    <label for="Choice2">Less</label>
                    
                    <input type="radio" id="Choice3"
                           name="expression" value="not equal">
                    <label for="Choice3">Not Equal</label>
                </div>
            
                <input type="submit" id="collection" name="collection" value="Get Collection">
            </fieldset>
        </form>
_COLLECTION_;
}

function delete_collection_form(): void
{
    echo <<<_DELETE_COLLECTION_
        <form method="POST" action="index.php">
            <fieldset>
                <legend>Delete People Collection</legend>
        
                <label for="delete_collection_id">Person ID to Delete People</label>
                <input type="number" id="delete_collection_id" name="delete_collection_id" min="1" required>
                
                <div>
                    Expression:
                    <input type="radio" id="Delete1"
                           name="delete_expression" value="more" checked>
                    <label for="Delete1">More</label>
            
                    <input type="radio" id="Delete2"
                           name="delete_expression" value="less">
                    <label for="Delete2">Less</label>
                    
                    <input type="radio" id="Delete3"
                           name="delete_expression" value="not equal">
                    <label for="Delete3">Not Equal</label>
                </div>
                
                <input type="submit" id="delete_collection" name="delete_collection" value="Delete Collection">
            </fieldset>
        </form>
_DELETE_COLLECTION_;
}