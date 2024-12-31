<?php
interface IDatabase {
    public function run_query(string $query): mysqli_result|bool;
    public function prepare(string $query, array $params): mysqli_stmt|bool;
    public function getConnection();
    public function getInsertId();
}
?>