<?php

namespace CloudCastle\Core\DB;

use CloudCastle\Core\App\App;
use CloudCastle\Core\Exceptions\DatabaseException;
use CloudCastle\Core\Resources\PaginateResource;
use CloudCastle\Core\Traits\Singleton;
use PDO;
use PDOException;
use PDOStatement;
use function CloudCastle\Core\config;

final class DB
{
    use Singleton;
    
    private array $config = [];
    private PDO $connection;
    
    /**
     * @throws DatabaseException
     */
    public static function init (): self
    {
        $config = config('data_base');
        self::$instance = new self();
        self::$instance->config = $config;
        self::$instance->setConnection();
        App::set('db', self::getInstance());
        App::set(self::class, self::getInstance());
        
        return self::getInstance();
    }
    
    /**
     * @throws DatabaseException
     */
    private function setConnection (): void
    {
        try {
            $this->connection = new PDO($this->config['dsn'], $this->config['username'], $this->config['password']);
        } catch (PDOException $e) {
            throw new DataBaseException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
    
    public function paginate (string $sql, array $binds, array $data): array
    {
        $limit = (int) $data['per_page'];
        $offset = (int) ((int) $data['page'] - 1) * $limit;
        
        $psql = "SELECT\n\tCOUNT(*) as total\nFROM ({$sql}) as paginate ";
        $stmt = $this->connection->prepare($psql);
        $stmt->execute($binds);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        if ($limit > 0) {
            $sql .= "LIMIT {$limit}\n";
            
            if ($offset > 0) {
                $sql .= "OFFSET {$offset}\n";
            }
        }
        
        $paginated = [
            'total' => $total,
            'page' => $data['page'],
            'per_page' => $limit,
        ];
        
        return [$this->get($sql, $binds), PaginateResource::make($paginated)];
    }
    
    public function get (string $sql, array $binds): array
    {
        $stmt = $this->query($sql, $binds);
        $data = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
            unset($row);
        }
        
        return $data;
    }
    
    public function query (string $sql, array $binds): PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($binds);
        
        return $stmt;
    }
    
    public function first (string $sql, array $binds): array|null
    {
        return $this->query($sql, $binds)->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}