<?php

class Database
{
    protected ?PDO $conn = null;

    public function __construct()
    {
        try
        {
             $this->conn = new PDO("sqlite:" . PROJECT_ROOT. "/" . DB_NAME,
                DB_USERNAME,
                DB_PASSWORD
             );
        }
        catch (PDOException $e)
        {
            exit("Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Select rows from a database.
     *
     * @param string $query
     * @param array $params
     * @return array|false
     */
    public function select(string $query = "", array $params = [])
    {
        $stmt = $this->executeStatement($query, $params);

        // maybe it should return just $stmt
        return $stmt->fetchAll();
    }

    /**
     * Prepare and execute generic statement.
     *
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    private function executeStatement(string $query, array $params = []): PDOStatement
    {
        $stmt = $this->conn->prepare($query);

        if (!$stmt)
        {
            throw new UnexpectedValueException("Unable to do prepared statement: " . $query);
        }

        if ($params)
        {
            $stmt->bindParam($params[0], $params[1]);
        }

        $stmt->execute();

        return $stmt;
    }
}
