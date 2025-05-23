<?php

namespace App\Snake;

class ExceptionGenerator
{
    public function randomMessage(): string
    {
        return collect([
            "TypeError: You can't add apples (int) and oranges (str), buddy.",
            "AttributeError: 'NoneType' object has no attribute 'anything', because it's literally *nothing*.",
            "KeyError: That key doesn’t exist. Try Hogwarts?",
            "IndexError: You reached beyond the list. Hope you packed snacks.",
            "ValueError: Couldn’t turn 'abc' into a number. Shocking, I know.",
            "NullPointerException: You tried to touch something that doesn’t exist. Again.",
            "ArrayIndexOutOfBoundsException: You're off the edge of the map, mate.",
            "NumberFormatException: “abc” is not a number. Not even close.",
            "ClassCastException: You tried to turn a cat into a toaster. Why?",
            "IllegalArgumentException: That's not just a bad argument, it's an *illegal* one.",
            "TypeError: undefined doesn’t have a 'foo'. Neither does your logic.",
            "ReferenceError: 'x' is not defined. Maybe define it? Just a thought.",
            "SyntaxError: Unexpected token '}'. Surprises are for birthdays, not syntax.",
            "RangeError: Stack overflow! Not the website. The *actual* one.",
            "TypeError: You tried changing a constant. The nerve.",
            "NullReferenceException: You tried to access null. Null is not your friend.",
            "IndexOutOfRangeException: You went too far, Frodo.",
            "ArgumentException: Your argument is invalid, and so is your attitude.",
            "InvalidOperationException: You changed the collection mid-iteration. Chaos ensues.",
            "DivideByZeroException: You broke math. Great job.",
            "NoMethodError: Nil has no methods. It’s a couch potato.",
            "NameError: You summoned a variable that doesn’t exist. Witchcraft?",
            "Parse error: Syntax error, unexpected 'awesome'. PHP wasn't ready for your brilliance.",
            "Warning: Division by zero. That’s how black holes happen.",
            "Notice: Undefined index: 'key'. Did you check your pockets?",
        ])->random();
    }
}
