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

             // sqlite 'forgets' to use foreign keys for backwards compatability
             $this->conn->exec("PRAGMA foreign_keys = ON");
        }
        catch (PDOException $e)
        {
            exit("Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Prepare and execute generic statement.
     *
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    protected function executeStatement(string $query, array $params = []): PDOStatement
    {
        $stmt = $this->conn->prepare($query);

        if (!$stmt)
        {
            throw new UnexpectedValueException("Failed to prepare query");
        }

        if ($params)
        {
            foreach ($params as $name => $value)
            {
                $stmt->bindValue($name, $value);
            }
        }

        if (!$stmt->execute())
        {
            throw new UnexpectedValueException("Statement execution failed");
        }

        return $stmt;
    }
}
