# Created By Cameron Jordan 2021

- Monitors Temperature of CPU on Raspberry Pi
- Can be stopped at any time with Ctrl + C

# How to use Arguments

- Arguments can be passed in the command line after specifying the file to run
- Arguments are case sensitive!
- For example: 'php monitor-temps.php V F=10000 I=3 C A'

# List of Arguments

> F
>> Specifies the max file size of the stored temperature file in Bytes
>> Default size is 32768 Bytes
>> **Example:** F=12345

> I
>> Specifies the interval the temperature monitoring stores the information in seconds
>> Default time is 2 seconds
>> **Example:** I=3

> O
>> Sets the log file to be overwritten when the max file size is reached instead of creating a new file
>> **Example:** O

> C
>> Disables colored text output in the terminal
>> **Example:** C

> A
>> Disables Ctrl + C prompt for advanced users who know how to stop the script execution
>> **Example:** A

> N
>> Bypasses intial store to file prompt without writing temperature information to file
>> **Example:** N

> Y
>> Bypasses intial store to file prompt and starts writing temperature information to file
>> **Example:** Y