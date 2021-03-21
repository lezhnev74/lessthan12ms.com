- date:Mar 13, 2017 21:24
- slug:one-step-towards-clean-architecture-from-rapid-application-development
# One step towards clean architecture from rapid application development
## The motivation for making this step
Having years of experience in rapid development in greenfield and sadly in brownfield projects, I realized that benefits of fast development at the beginning, become issues with maintaining and introducing new features later in the project life. 

I wouldn't consider changing anything but since I am lazy (as many developers) I found that I'd better design my app in a clean way beforehand and save time later. This is just my choice which has other benefits like expected behavior, ease of changing and so on. But at the end of the day, this is just my time and peace of mind.

## The problem with rapid development
When you solve problems in a rapid-development-way you put logic anywhere - in the controllers, in models, in "managers", "services", "libs" etc. The problem with that is repeating the same code in many places and leaking a lot of implementation details. 

To reduce both things I defined my first step towards "cleaner architecture". 

## Decouple commands and queries in isolated objects with public APIs
So anything that is going on in the project is a result of intention of the user (whoever he is). Consider verbs "Signup this user", "Get all premium users", "Cancel this order" etc.
All these commands must be represented by dedicated classes. To protect any leaking things, I define clear interfaces of each "Command".

<a href="https://lessthan12ms.com/wp-content/uploads/2017/03/request-response-command-1.jpg"><img src="https://lessthan12ms.com/wp-content/uploads/2017/03/request-response-command-1.jpg" alt="" width="800" height="484" class="aligncenter size-full wp-image-438" /></a>

By having the Request object, Response object and Handler I formulate a clean OOP architecture. Any client can call this commands by conforming its API. This is clean and safe. By having known commands any developer can easily get what is going on in the system and reuse them later (thus reducing code duplication). If I have dedicated command for something I can mix it with other commands (without touching their code) and get desired results. This is nice.


## This step requires writing more code
So when I define a "command" I actually define 3 things:

* Request
* Response
* and Command itself

I don't use any terms from service oriented architecture (service buses). This is just my simple artifacts:

* **Request** makes sure input is validated and known to Command
* **Response** makes sure calling user will always receive expected data
* **Command** makes the job by evaluating the request and formulating the response. Clean and nice.

But I forgot the most important additional file - a test file. At least one file which tests request, response and command behavior. This is an essential part of all this. By making a command I always produce a test which protects the code from unexpected changes. 

## Still I want to save time by generating all these 4 classes
Ok, I have decided to formulate any user's intent via my own tested Commands which I can reuse. I tried to make all those files manually for each command I need. But this eventually bored me to death and my lazy (in a good sense) nature suggested to use code generators. 

Well, there are few options. I may want to mention two of them:

* There is a nice package for Laravel framework (yes, if you practice RAD then you probably use Laravel) which helps you generate operation. It is called [Lucid](https://github.com/lucid-architecture/laravel). [I blogged about it earlier](https://lessthan12ms.com/my-php-code-structure-should-tell-you-what-it-does-or-how-do-i-show-projects-intents-to-my-teammates/)
* You can write your own generator. Well, at least [I did](https://github.com/lezhnev74/ddd-generator).

## Step is made. What changed?
Well, I feel much more comfortable having tested isolated, independent commands. I know that I can reuse them in complex domains. Or I can change internal things with no fear because tests will get me covered. 

Any new idea, task or feature starts with thinking (which is a good sign, you know) about if I have a command for that, or I can reuse existing commands or I need to make a new one. 

This is a clean way of developing. I still move towards clean architecture and study DDD and TDD, and all this post may be considered as my practical results of such study. 

I hope this will be of use for you as well.