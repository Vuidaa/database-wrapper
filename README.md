Database-Wrapper
================

Mysql database wrapper for most common and simple SQL queries;

Some examples:
=============

Select all records form our table:
```
DB::getInstance->Select('sometable')->Run(PDO::FETCH_ASSOC);
```
Select only name and id columns from our table where id is more than 10 :
```
DB::getInstance->Select('sometable',array('id','name'))->Where(array('id','>','10'))->Run();
```
Insert query:
```
DB::getInstance->Insert('tablename',array('name','email','address'))->Run();
```
Delete some records:
```
DB::getInstance->Delete('tablename')->Where('name','=','someValue')->Run();
```
This wrapper is made just for educational purposes so it my contain some minor errors, so i do not recommend you to use it in production environment;

Feel free to modify, extend or do whatever you like :)
