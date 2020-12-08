<?php
/*
 * MIT License
 *
 * Copyright (c) 2020. Nils Witt
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Class VplanEntry
 */
class VplanEntry implements JsonSerializable
{
    private $date;
    private $lessons = array();
    private $teacher = "---";
    private $newTeacher = "---";
    private $grade;
    private $course;
    private $room = "---";
    private $info = "---";
    private $subject = "sbj";
    private $newSubject = "---";
    private $type = "---";

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            "date" => $this->date,
            "lessons" => $this->lessons,
            "teacher" => $this->teacher,
            "newTeacher" => $this->newTeacher,
            "grade" => $this->grade,
            "course" => $this->course,
            "room" => $this->room,
            "info" => $this->info,
            "subject" => $this->subject,
            "newSubject" => $this->newSubject,
            "type" => $this->type,
        ];
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getNewSubject()
    {
        return $this->newSubject;
    }

    /**
     * @param mixed $newSubject
     */
    public function setNewSubject($newSubject): void
    {
        $this->newSubject = $newSubject;
    }


    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info): void
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getNewTeacher()
    {
        return $this->newTeacher;
    }

    /**
     * @param mixed $newTeacher
     */
    public function setNewTeacher($newTeacher): void
    {
        $this->newTeacher = $newTeacher;
    }

    /**
     * @return array
     */
    public function getLessons(): array
    {
        return $this->lessons;
    }

    /**
     * @param array $lessons
     */
    public function setLessons(array $lessons): void
    {
        $this->lessons = $lessons;
    }

    /**
     * @return mixed
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * @param mixed $teacher
     */
    public function setTeacher($teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade): void
    {
        $this->grade = $grade;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param mixed $course
     */
    public function setCourse($course): void
    {
        $this->course = $course;
    }

    /**
     * @return mixed
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param mixed $room
     */
    public function setRoom($room): void
    {
        $this->room = $room;
    }


}