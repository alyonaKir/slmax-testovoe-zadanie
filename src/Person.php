<?php
declare(strict_types=1);

namespace User\TestProject;

class Person
{
    private int $id;
    private string $name;
    private string $surname;
    private string $birthday;
    private int $gender;
    private string $birthcity;
    public static $dbInfo;

    /**
     * @param int $id
     * @param string $name
     * @param string $surname
     * @param string $birthday
     * @param int $gender
     * @param string $birthcity
     */
    public function __construct(int $id, string $name, string $surname, string $birthday, int $gender, string $birthcity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->birthday = $birthday;
        $this->gender = $gender;
        $this->birthcity = $birthcity;
        if ($this->validation()) {
            $this->checkPersonById();
        } else {
            echo "Check your fields.";
        }

    }


    public static function createConnection()
    {
        self::$dbInfo = require 'dbInfo.php';
        $conn = new \mysqli(self::$dbInfo['host'], self::$dbInfo['user'], self::$dbInfo['password'], self::$dbInfo['base']);
        if ($conn->connect_error) {
            die("Error: " . $conn->connect_error);
        }
        return $conn;
    }

    private function validation(): bool
    {
        if ($this->id <= 0
            || empty($this->name)
            || empty($this->surname)
            || empty($this->birthday)
            || ($this->gender != 1 && $this->gender != 0)
            || empty($this->birthcity)) {
            return false;
        }
        if (preg_match('/\d/', $this->name) === 1
            || preg_match('/\d/', $this->surname) === 1
            || preg_match('/\d/', $this->birthcity) === 1
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->birthday) !== 1
        ) {
            return false;
        }

        return true;
    }

    private function checkPersonById(): void
    {
        $conn = self::createConnection();
        $db_table = self::$dbInfo['table'];
        $sql = "SELECT * FROM $db_table WHERE id=$this->id";

        if ($result = $conn->query($sql)) {
            if ($result->num_rows != 0) {
                foreach ($result as $row) {
                    $this->name = $row["name"];
                    $this->surname = $row["surname"];
                    $this->birthday = $row["birthday"];
                    $this->gender = (int)$row["gender"];
                    $this->birthcity = $row["birthcity"];
                }
            } else {
                $this->add();
            }
            $result->free();
            $log = "Successful " . date("Y-m-d H:i:s");
        } else {
            $log = "Error: " . $conn->error . date("Y-m-d H:i:s");
        }
        $conn->close();
        self::addLogsToFile($log);
    }

    public static function birthDateToAge(string $birthDate): int
    {
        $birthday_timestamp = strtotime($birthDate);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }
        return $age;
    }

    public static function genderToString(int $gender): string
    {
        return match ($gender) {
            0 => 'муж',
            1 => 'жен'
        };
    }

    public function delete(): void
    {
        $conn = self::createConnection();
        $db_table = self::$dbInfo['table'];
        $sql = "DELETE FROM $db_table WHERE id=$this->id";
        if ($conn->query($sql) === TRUE) {
            $log = "Record deleted successfully " . date("Y-m-d H:i:s");
        } else {
            $log = "Error deleting record: " . $conn->error . date("Y-m-d H:i:s");
        }
        self::addLogsToFile($log);
        $conn->close();
    }

    public function add(): void
    {
        $conn = self::createConnection();
        $db_table = self::$dbInfo['table'];

        $name = $conn->real_escape_string($this->name);
        $surname = $conn->real_escape_string($this->surname);
        $birthday = $conn->real_escape_string($this->birthday);
        $gender = $conn->real_escape_string((string)$this->gender);
        $birthcity = $conn->real_escape_string($this->birthcity);


        $sql = "INSERT INTO $db_table(name, surname, birthday, gender, birthcity)" .
            "VALUES ('$name','$surname', '$birthday', '$gender', '$birthcity')";

        if ($result = $conn->query($sql)) {
            $this->id = $conn->insert_id;
            $log = "Successfully edited " . date("Y-m-d H:i:s");
        } else {
            $log = "Ошибка: " . $conn->error . date("Y-m-d H:i:s");
        }
        $conn->close();
        self::addLogsToFile($log);
    }

    public function formatPerson(string $parameter): \stdClass
    {
        $result = new \stdClass();

        $result->id = $this->id;
        $result->name = $this->name;
        $result->surname = $this->surname;
        $result->birthcity = $this->birthcity;
        switch ($parameter) {
            case 'gender':
                $result->gender = self::genderToString($this->gender);
                break;
            case 'age':
                $result->age = self::birthDateToAge($this->birthday);
                break;
            case 'both':
                $result->gender = self::genderToString($this->gender);
                $result->age = self::birthDateToAge($this->birthday);
                break;
            default:
                echo 'There are only three possible parameters: \'gender\', \'age\', \'both\'';
        }
        $result->birthday = $this->birthday;
        $result->gender = self::genderToString($this->gender);
        return $result;
    }

    public static function addLogsToFile($data, $filename = __DIR__ . '/database_logs.log'): void
    {
        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename, 0777);
        }
        file_put_contents($filename, $data . PHP_EOL, FILE_APPEND);
    }
}
