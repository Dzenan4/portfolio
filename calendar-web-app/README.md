[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/oEgWAMae)
# CSE330
Dzenan Zecevic 509227 Dzenan4

<strong>Links:</strong>

1. To calculator: http://ec2-18-223-125-27.us-east-2.compute.amazonaws.com/~dzenanz4/module5-individual-Dzenan4/CalculatorJS/calculator.html 

2. To weather widget: http://ec2-18-223-125-27.us-east-2.compute.amazonaws.com/~dzenanz4/module5-individual-Dzenan4/WeatherJS/weather.html

3. To calendar: http://ec2-18-223-125-27.us-east-2.compute.amazonaws.com/~dzenanz4/module5-group-module5-509227/calender.html

<strong>Creative Portion Description:</strong>

For my creative portion, I implemented:

1. Descriptive tags that could be added to events, such as "Appointment", "Work", and "School"

2. Color options that could be added to events and are visible in the calendar. These would allow users to quickly 
   determine the purpose of an event based on its color. 

3. The ability to share a calendar with other users. Simply clicking on the "Share Calendar" button prompts the 
   user for a username to be entered. This username is the username of the person who the current user wishes
   to share their calendar with. If a valid username is entered, the user's entire calendar is shared and 
   represented in the other person's calendar. If an invalid username is entered, an alert is displayed.

4. The ability to create group events. Upon pressing the "+ Add Event" button, a modal pops up with a
   checkbox option for marking the new event as a group event. When this box is checked, an input for 
   entering other user's usernames is displayed. A user can add as many users as they want to their
   group event. All valid users will experience the event added to their calendar.

<strong>Points I figured I should make:</strong>

1. When it came to preventing XSS attacks, I made sure to filter any input that would eventually be displayed in HTML. 
   For this project in particular, the only variable that the user can type on their own that will be displayed later
   is the title of an event. For that reason, I only used htmlentities() on the titles of events being displayed 
   to the user. Other variables, such as date, time, tag and color, could not be modified with text, making it 
   impossible to take advantage of them for the purpose of an XSS attack. 

2. When deleting an event shared with you, all shared events are deleted as a way of making it easier to unshare 
   a calendar and not worry about deleting several individual events. 

3. Group events can only be modified by the person who shared the group event. This disables other users from altering the 
   contents of the group event and negatively impacting it for the rest of the group.


<br><br><br><br><br><br><br><br><br>
Rubric


# Grading

| Possible | Requirement                                                                                                     | 
 | -------- | --------------------------------------------------------------------------------------------------------------- | 
| 5        | Calendar is displayed as a table grid with days as columns and weeks as rows, one month at a time               | 
| 5        | User can view different months as far in the past or future as desired                                          | 
| 5        | Events can be added, modified, and deleted                                                                      | 
| 2        | Events have a title, date, and time                                                                             | 
| 8        | Users can log into the site and they cannot view or manipulate events associated with other users               | 
| 10       | All actions are performed over AJAX, without ever needing to reload the page                                    |
| 2        | Code best practices (formatting, commenting, readability) and use of `fetch` and no `var`                       | 
| 2        | If storing passwords, they are stored salted and encrypted                                                      | 
| 3        | All AJAX requests that contain sensitive info or modify something on the server are performed via POST, not GET | 
| 3        | Safe from XSS attacks (all content is escaped on output)                                                        | 
| 2        | Safe from SQL injection attacks (must use prepared statements)                                                  | 
| 3        | CSRF token implemented OR User-Agent implementation                                                             | 
| 3        | Session cookie is HTTP-only                                                                                     | 
| 2        | Page passes W3C validator                                                                                       | 
| 4        | Site is intuitive and easy to use/navigate                                                                      | 
| 1        | Site is visually appealing                                                                                      |

## Creative Portion (15 possible)
