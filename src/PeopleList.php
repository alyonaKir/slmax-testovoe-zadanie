<?php
declare(strict_types=1);

namespace User\TestProject;

use Exception;

class PeopleList
{
    private array $peopleIds;

    /**
     * @param int $id
     * @param string $sign
     * @throws Exception
     */
    public function __construct(int $id, string $sign)
    {
        if (class_exists(Person::class)) {
            $db_table = Person::$dbInfo['table'];
            switch ($sign) {
                case '>':
                    $this->setPeopleIds("SELECT * FROM $db_table WHERE id > $id");
                    break;
                case '<':
                    $this->setPeopleIds("SELECT * FROM $db_table WHERE id < $id");
                    break;
                case '=':
                    $this->setPeopleIds("SELECT * FROM $db_table WHERE id = $id");
                    break;
                default:
                    echo 'You can use only >, < and = signs';
            }
        } else {
            throw new Exception('The class Person does not exist');
        }
    }

    private function setPeopleIds(string $sql): void
    {
        $conn = Person::createConnection();

        if ($result = $conn->query($sql)) {
            if ($result->num_rows != 0) {
                foreach ($result as $row) {
                    $this->peopleIds[] = $row["id"];
                }
            }
            $result->free();
            $log = "Successful" . date("Y-m-d H:i:s");
        } else {
            $log = "Error: " . $conn->error . date("Y-m-d H:i:s");
        }
        $conn->close();
        Person::addLogsToFile($log);
    }

    public function getPeopleByIds(): array
    {
        $conn = Person::createConnection();
        $db_table = Person::$dbInfo['table'];
        $resultPeople = array();
        for ($i = 0; $i < count($this->peopleIds); $i++) {
            $id = (int)$this->peopleIds[$i];
            $sql = "SELECT * FROM $db_table WHERE id = $id";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {

                    foreach ($result as $row) {
                        $resultPeople[] = new Person(
                            (int)$row["id"],
                            $row["name"],
                            $row["surname"],
                            $row["birthday"],
                            (int)$row["gender"],
                            $row["birthcity"]
                        );

                    }
                }
                $result->free();
                $log = "Successful get" . date("Y-m-d H:i:s");
            } else {
                $log = "Error: " . $conn->error . date("Y-m-d H:i:s");
            }
            Person::addLogsToFile($log);
        }
        $conn->close();
        return $resultPeople;
    }

    public function deleteByPeopleIds(): void
    {
        foreach ($this->getPeopleByIds() as $people) {
            $people->delete();
        }
    }
}
