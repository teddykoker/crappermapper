<?php

class Helpers
{
	private static $config;

	/**
	* Initializes config given the assosiative array of 
	**/
	public static function init($config)
	{
		if (isset(self::$config))
		{
			trigger_error("Helpers is already initialized", E_USER_ERROR);
		}
		self::$config = $config;
	}

	/**
    * Executes SQL statement, possibly with parameters, returning
    * an array of all rows in result set or false on (non-fatal) error.
    *
    * Taken with permission from CS50's PHP library.
    */
	public static function query(/* $sql [, ... ] */)
	{
        // ensure library is initialized
		if (!isset(self::$config))
		{
			trigger_error("CS50 Library is not initialized", E_USER_ERROR);
		}

        // ensure database is configured
		if (!isset(self::$config["database"]))
		{
			trigger_error("Missing value for database", E_USER_ERROR);
		}
		foreach (["host", "name", "password", "username"] as $key)
		{
			if (!isset(self::$config["database"][$key]))
			{
				trigger_error("Missing value for database.{$key}", E_USER_ERROR);
			}
		}

        // SQL statement
		$sql = func_get_arg(0);

        // parameters, if any
		$parameters = array_slice(func_get_args(), 1);

        // try to connect to database
		static $handle;
		if (!isset($handle))
		{
			try
			{
                // connect to database
				$handle = new PDO(
					"mysql:dbname=" . self::$config["database"]["name"] . ";host=" . self::$config["database"]["host"],
					self::$config["database"]["username"],
					self::$config["database"]["password"]
					);
			}
			catch (Exception $e)
			{
                    // trigger (big, orange) error
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}

       	// ensure number of placeholders matches number of values
        // http://stackoverflow.com/a/22273749
        // https://eval.in/116177
		$pattern = "
                /(?:
                '[^'\\\\]*(?:(?:\\\\.|'')[^'\\\\]*)*'
                | \"[^\"\\\\]*(?:(?:\\\\.|\"\")[^\"\\\\]*)*\"
                | `[^`\\\\]*(?:(?:\\\\.|``)[^`\\\\]*)*`
                )(*SKIP)(*F)| \?
                /x
            ";

		preg_match_all($pattern, $sql, $matches);
		if (count($matches[0]) < count($parameters))
		{
			trigger_error("Too few placeholders in query", E_USER_ERROR);
		}
		else if (count($matches[0]) > count($parameters))
		{
			trigger_error("Too many placeholders in query", E_USER_ERROR);
		}

            // replace placeholders with quoted, escaped strings
		$patterns = [];
		$replacements = [];
		for ($i = 0, $n = count($parameters); $i < $n; $i++)
		{
			array_push($patterns, $pattern);
			array_push($replacements, preg_quote($handle->quote($parameters[$i])));
		}
		$query = preg_replace($patterns, $replacements, $sql, 1);

            // execute query
		$statement = $handle->query($query);
		if ($statement === false)
		{
			trigger_error($handle->errorInfo()[2], E_USER_ERROR);
		}

            // if query was SELECT
            // http://stackoverflow.com/a/19794473/5156190
		if ($statement->columnCount() > 0)
		{
                // return result set's rows
			return $statement->fetchAll(PDO::FETCH_ASSOC);
		}

            // if query was DELETE, INSERT, or UPDATE
		else
		{
                // return number of rows affected
			return $statement->rowCount();
		}
	}
}


?>