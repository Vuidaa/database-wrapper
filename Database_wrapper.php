  <?php
/**
* @author Vaidas Cesna;
* 
* Mysql database wrapper for most common and simple SQL queries;
* 
* This wrapper is made just for educational purposes so it my contain some minor errors,
* so i do not recommend you to use it in production environment;
*
* Feel free to modify, extend or do whatever you like :)
*
*/
class DB
{

    /**
    * Get instance of our class;
    * @var null (by default);
    */

    private static $instance = null;
    
    /**
    * Host name;
    * @var String;
    */

    private $host = '';

    /**
    * Our database name;
    * @var String;
    */

    private $dbname = '';

    /**
    * Username;
    * @var String
    */

    private $user = '';

    /**
    * Password;
    * @var String
    */

    private $password = '';
    
    /**
    * PDO Instance of PDO class;
    * @var Object;
    */
    public $db;

    /**
    * SQL query string;
    * @var String
    */

    public  $sql = '';

    /**
    * Query type;
    * @var String
    */

    private $querytype = '';

    /**
    * List of parameters to bind;
    * @var Array;
    */

    private $paramToBind = array();

    /**
    * Update and Delete queries must use (in most of cases) where clause;
    * @var Boolean;
    */

    private $Where = false;
    
    /**
    * __construct;
    *
    * Initialize instance of PDO clas to $db variable; 
    *
    */

    public function __construct()
    {
        try 
        {
            $this->db = new PDO("mysql:host=$this->host;dbname=$this->dbname", "$this->user", "$this->password");
            $this->db->setattribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            die($e->getmessage());
        }
    }
    
    /**
    * getInstance;
    * 
    * Singleton pattern is very useful here if we want to create only one instance of DB class;
    *
    */
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new DB();
        }

        return self::$instance;
    }
    
    /**
    *   Createtable;
    *
    *  Method to create new table;
    *
    * @param String $tablename - Name of table;
    * @param Array  $fields - Associative array filled with field name(key) and field properties(value);
    * @return boolean true;
    * @example: 
    * DB::getInstance()->createTable('newtable',array('id'=>'int(255), PRIMARY KEY, AUTO_INCREMENT', 'name'=>'varchar(255), UNIQUE'));
    *
    */

    public function createTable($tablename, $fields = array())
    {   //Let's make sure we got the right parameters
        if (func_num_args() == 2 && is_array($fields))
        {

                try
                {
                    $query = "CREATE TABLE $tablename (";
                    
                    foreach ($fields as $key => $value)
                    {
                        $query .= ' ' . $key . ' ';
                        $query .= str_replace(',', ' ', $value);
                        $query .= ',';
                    }
                    
                    $query = rtrim($query, ',') . ')';

                    $this->db->query($query);

                    return true;

                    throw new PDOException();
                }
                catch(PDOException $e)
                {
                    echo $e->getmessage();
                }
            
        }
        die();
    }

    /**
    *   Select;
    *
    *   SQL SELECT statement wrapper;
    * 
    * @param String $table - Name of the table to select from;
    * @param Array $columns - List of columns to be selected;
    * @return Object, class, array - depending on fetching style providede in run() method;
    * @example:
    * DB::getInstance()->Select('sometable',array('id','name','password'))->run() - selects only columns specified in array;
    * DB::getInstance()->Select('sometable')->run(PDO::FETCH_NUM); - selects all columns;
    *
    */

    public function Select($table, $columns = array())
    {   //Let's make sure we got the right parameters
        if(is_array($columns) && is_string($table))
        {   
            switch (func_num_args()) 
            {
                case 1:

                    $this->querytype = "SELECT";
                    $this->sql.= "SELECT * FROM `$table`";
                    
                    return $this;
                    break;
                
                case 2:

                    $columns = rtrim(implode(',', $columns),',');
                    
                    $this->querytype = "SELECT";
                    $this->sql.= "SELECT $columns FROM `$table`";

                    return $this; 
                    break;

                default:

                    die();
                    break;
            }
        }

        die();
    }
    /**
    *   Update;
    *   
    *   SQL UPDATE statement wrapper;
    *   
    * @param String $table - Table name;
    * @param Array $set - Array filled with other arrays containing new data for each field. 
    * @return Boolean true;
    * @example:
    * DB::getInstance()->update('sometable,
    *			array(array('id','=','12'), array('name','=','Somename'), array('email','=','someemail@gmail.com')))->
    *			where(array('id','=','25'))->run();
    *
    *	Note: You must declare 'where' clause in order to run your query successfully;
    */
    public function Update($table, $set = array())
     {  //Let's make sure we got the right parameters
        if(is_string($table) && is_array($set) && !empty($set))
        {
            $this->querytype = "UPDATE";
            $this->sql = "UPDATE `$table` SET ";

            foreach ($set as $array)
            {
                if(!is_array($array))
                {
                    die();
                }

               foreach ($array as $key => $value)
               {
                    if($value == '')
                    {
                        die();
                    }

                    if($key == '2')
                    {
                        $this->sql.= ' ? ';
                        $this->paramToBind[] = $value;
                    }
                    else
                    {
                        $this->sql.= $value;
                    }      
               }
               $this->sql .=',';
           }

           $this->sql = rtrim($this->sql, ',');

           return $this;
        }   
        die();
    }

    /**
    *	Insert;
    *
    *	SQL INSERT statement wrapper;
    *
    * @param String $table - Table name;
    * @param Array $columns - Optional - Insert data only in specified columns;
    * @param Array $values - Values to be inserted;
    * @return Boolean true;
    * @example: 
    * DB::getInstance()->Insert('sometable', array('email','name'),array('someemail','somename'))->run();
    * DB::getInstance()->Insert('sometable', array('someid','somename','someemail','somegendre','someBOD'))->run();	
    *	
    */
    public function Insert($table, $columns = array(), $values = array())
    {  
        //Let's make sure we got the right parameters
        if(is_string($table) && is_array($columns) && is_array($values))
        {
            switch (func_num_args()) 
            {
                case 2:

                    $valueslist = '';
                    $this->querytype = "INSERT INTO";

                    $values = func_get_arg(1);

                    foreach($values as $valueToBind)
                    {
                        $valueslist.= '?,';

                        $this->paramToBind[] = $valueToBind; 
                    }

                    $valueslist = rtrim($valueslist, ',');
                    
                    $this->sql = "INSERT INTO $table VALUES($valueslist)";

                    return $this;
                    break;

                case 3:

                    $valueslist = '';
                    $this->querytype = "INSERT INTO";

                    foreach($values as $valueToBind)
                    {
                        $valueslist .= '?,';
                        $this->paramToBind[] = $valueToBind;
                    }

                    $columns = rtrim(implode(',', $columns),',');
                    $valueslist = rtrim($valueslist,',');

                    $this->sql = "INSERT INTO `$table`($columns) VALUES($valueslist)";

                    return $this;
                    break;

                default:
                    die();
            }
        }
        die();
    }

    /**
    *	Delete.
    *
    *	SQL DELETE statement wrapper;
    *
    * @param String $table - Table name;
    * @return Boolean - true;
    * @example: 
    * DB::getInstance()->delete('sometable')->where(array('fieldname','=','somename'))->run();	
    *
    * Note: You must declare where clause in order to run your query successfully;
    */
    public function Delete($table)
    {
        if(is_string($table))
        {
            $this->querytype = "DELETE";
            $this->sql = "DELETE FROM $table";

            return $this;
        }
    }

    /**
    *	Select Distinct.
    *
    *	SQL SELECT DISTINCT statement wrapper;
	*
    * @param String $table - Table name;
    * @param Array $fields - Field names to be selected;
    * @return Object, class, array - depending on fetching style providede in run() method;
    * @example: 
    * DB::getInstance()->SelectDistinct('sometable',array('columnname','anothercolumnname'))->run();	
	*
    */

    public function SelectDistinct($table,$columns = array())
    {   //Let's make sure we got the right parameters
        if(is_string($table) && is_array($columns))
        {
            $columns = rtrim(implode(',', $columns),',');
            
            $this->querytype = "SELECT";
            $this->sql = "SELECT DISTINCT $columns FROM $table";

            return $this;
        }
        die();
    }

    /**
    *	InsertIntoSelect.
    *
    *	SQL INSERT INTO Select statement wrapper;
	*
    * @param String $tableToInsert - Table to insert new records;
    * @param String $tableToSelect - Table to select records from;
    * @param Array $insertFields - Optional - Insert into specific columns only;
    * @param Array $selectFields - Optional - Select only specific columns;
    * @return Boolean True;
    * @example: 
    * DB::getInstance()->InsertInto('emptytable','fulltable',array('id','name'),array('id','name'))->run(); - copies id and name columns from `fulltable` to `emptytable`;
    * DB::getInstance()->InsertInto('emptytable','fulltable')->run(); - copies ALL records from `fulltable` to `emptytable`;
	*
    */

    public function InsertIntoSelect($tableToInsert, $tableToSelect, $insertFields = array(), $selectFields = array())
    {
        switch (func_num_args()) 
        {
            case 2:
                //Let's make sure we got the right parameters
                if(is_string($tableToInsert) && is_string($tableToSelect))
                {
                    $this->querytype = "INSERT INTO";
                    $this->sql = "INSERT INTO $tableToInsert SELECT * FROM $tableToSelect";

                    return $this;
                }
                break;

            case 4:
                //Let's make sure we got the right parameters
                if(is_string($tableToInsert) && is_string($tableToSelect) && is_array($insertFields) && is_array($selectFields))
                {
                    $this->querytype = "INSERT INTO";

                    $insertFields = rtrim($insertFields = implode(',', $insertFields),',');
                    $selectFields = rtrim($selectFields = implode(',', $selectFields),',');

                    $this->sql = "INSERT INTO $tableToInsert ($insertFields) SELECT $selectFields FROM $tableToSelect";

                    return $this;
                }
                break;
        }
        die();
    }

    /**
    *   Join.
    *
    *   SQL Join statement wrapper;
    *
    * @param Array $columns - list of columns to be selected;
    * @param String $tableFrom - Table to select from;
    * @param String $joinType - Join type - INNER JOIN, LEFT JOIN, RIGHT JOIN, FULL JOIN;
    * @param String $tableJoin - Table to join;
    * @param Array $on - On clause;
    * @return Boolean True;
    * @example: 
    * DB::getInstance()->Join(array('sometable.id','sometable2.email'),'sometable','LEFT JOIN','sometable2',array('sometable.name','=','sometable2.name')->run();
    *
    */

    public function Join($columns = array(), $tableFrom, $joinType, $tableJoin, $on = array())
    {
        if(func_num_args() == 5)
        {   //Let's make sure we got the right parameters
            if(is_array($columns) && is_array($on) && !empty($columns) && count($on) == 3)
            {
                $this->querytype = "SELECT";

                $columns = implode(', ', $columns);
                $joins = array('INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL JOIN');
              
                if(in_array(strtoupper($joinType), $joins))
                {
                    $this->sql = "SELECT $columns FROM $tableFrom $joinType $tableJoin ON $on[0] $on[1] $on[2]";

                    return $this;
                }
            }
            die();
        }
        die();
    }

    /**
    *   Where;
    *
    *   SQL WHERE clause wrapper;
    * 
    * @param Single array, or array and string mixed together;
    * @return Current object
    * @example: 
    * DB::getInstance()->Select('sometable')->where(array('id','>','5'))->run();
    * DB::getInstance()->Select('sometable')->where(array('id','>','5'),'OR',array('id','=','12'))->run();
    * DB::getInstance()->Select('sometable')->where(array('id','>','5'),'AND',array('name','=','SomeName'))->run();
    *
    */

    public function Where()
    {   //Let's make sure we got the right parameters
        if(func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            $whereClause = func_get_arg(0);
            $marks = array('=','<','>','<=','>=','BETWEEN','LIKE','IN');

            if(in_array($whereClause[1], $marks) && count($whereClause) == 3)
            {
                $this->sql .=" WHERE $whereClause[0] $whereClause[1] ? ";

                $this->paramToBind[]= $whereClause[2];

                $this->Where = true;

                return $this;          
            }

        }
        elseif(func_num_args() >= 3)
        {   
            $marks = array('=','<','>','<=','>=','BETWEEN','LIKE','IN');
            $numOfAllArgs = func_num_args();
            $this->sql .= " WHERE ";

            for($i = 0; $i <= $numOfAllArgs -1; $i++)
            {
                if(!is_int($i / 2))
                {
                    if(strtoupper(trim(func_get_arg($i))) == 'OR' || strtoupper(trim(func_get_arg($i))) == 'AND')
                    {
                        $this->sql .= ' '.strtoupper(func_get_arg($i)). ' ';
                    }
                    else
                    {
                        die();
                    }
                }
                elseif(is_array(func_get_arg($i)))
                {
                    $this->sql .= func_get_arg($i)[0];

                    if(in_array(func_get_arg($i)[1], $marks))
                    {
                        $this->sql .= func_get_arg($i)[1];
                    }
                    else
                    {
                        die();
                    }

                    $this->sql .=  " ? ";
                    $this->paramToBind[] = func_get_arg($i)[2];
                    $this->Where = true;
                }
                

            }
            return $this;

        }
        else
        {
            return die();
        }
    }
    
    

    /**
    *   Count;
    *
    *   Method to count total number of fields;
    * 
    * @param String $table - Table name;
    * @return Int - Total number of fields in provided table;
    * @example: 
    *  DB:getinstance()->count('sometablename');
    *
    */

    public function Count($table)
    {
        if (is_string($table)) 
        {
            $query = $this->db->query("SELECT * FROM `$table`");
                
            return $query->rowCount();
        } 
    }
    
    /**
    *   OrderBy;
    *
    *   Order selected fields.
    * 
    * @param Array $columns - Single or multiple columns;
    * @param String $sort - ASC or DESC;
    * @return Current object.
    * @example: 
    * DB:getinstance()->Select('sometable')->OrderBy(array('id'), 'DESC')->run();
    * DB:getinstance()->Select('sometable')->OrderBy(array('id', 'email'))->run();
    *
    */

    public function OrderBy($columns = array(), $sort = 'ASC')
    {   //Let's make sure we got the right parameters
        if(is_array($columns) && is_string($sort))
        {
            $columns = rtrim(implode(',', $columns),',');

            $this->sql.= " ORDER BY $columns $sort";

            return $this;
        }

        die();
    }


    /**
    *   Run;
    *
    *   Main method to actually execute our query;
    * 
    * @param Constant - $fetchStyle - Specify fetching style for SELECT type queries;
    * @example:
    * DB::getInstance()->Select('sometable',array('name','email'))->run(PDO::FETCH_ASSOC);
    * DB::getInstance()->Insert('sometable',array('name','email','address'))->run();
    * DB::getInstance()->Update('sometable',array(array('name','=','newname'),array('pass','=','newpass')))->run();
    *
    * This is the main method if you want your query to be executed;
    *
    * Note - You don't need to run this method for Count() method;
    *
    */
    
    public function Run($fetchStyle = PDO::FETCH_NUM)
    {
        if($this->sql != '')
        { 
            try
            {
                switch ($this->querytype) 
                {
                case 'SELECT':
                    $prepare = $this->db->prepare($this->sql);
                    if($this->Where)
                    {
                        $prepare->execute($this->paramToBind);
                    }
                    else
                    {
                        $prepare->execute();
                    }
                        unset($this->sql);
                        unset($this->paramToBind);
                        unset($this->querytype);
                        unset($this->table);
                        
                        return $prepare->fetchall($fetchStyle);
                        break;

                case 'INSERT INTO':
                    $prepare = $this->db->prepare($this->sql);
                    if($prepare->execute($this->paramToBind))
                    {   
                        unset($this->sql);
                        unset($this->paramToBind);
                        unset($this->querytype);

                        return true;
                    }
                        break;

                case 'UPDATE':
                    if($this->Where)
                    {
                        $prepare = $this->db->prepare($this->sql);
                        if($prepare->execute($this->paramToBind))
                        {
                            unset($this->sql);
                            unset($this->paramToBind);
                            unset($this->querytype);

                            return true;
                        }
                    }
                        else
                        {
                            die();
                        }
                        break;

                case 'DELETE':
                    if($this->Where)
                    {
                        $prepare = $this->db->prepare($this->sql);
                        if($prepare->execute($this->paramToBind))
                        {

                            unset($this->sql);
                            unset($this->paramToBind);
                            unset($this->querytype);
                            unset($this->table);

                            return true;
                        }else
                        {
                            die();
                        }
                            break;
                    }
                }
                   
                throw new PDOException();
            }
            catch(PDOException $e)
            {
                echo $e->getmessage();
            }     
        } 
    }
}  

?>