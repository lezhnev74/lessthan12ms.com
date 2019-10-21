slug: my-php-code-structure-should-tell-you-what-it-does-or-how-do-i-show-projects-intents-to-my-teammates
date: Jan 1, 2017 17:15
# My PHP code structure should tell you what it does - or how do I show project's intents to my teammates?
<p>That is not a secret that popular frameworks like Laravel, Symfony and the rest greatly increase the speed of development. But it comes with some side effects. For example, Laravel framework suggests to you to follow some built-in default folder structure and put Controllers to the <code>app/http/Controllers</code> and Jobs to the <code>app/Jobs</code> folder. The problem here is that by looking at folder structure you basically cannot say what the project does. There are few things you can do to let your teammate catch up on the project with much fewer efforts by showing the intents of the code on structure level.<!--more--></p>
<h2>Laravel's default folder structure (as of Dec 2016)</h2>
<p>I will write about Laravel framework just because I use it a lot. Take a look at this normal folder structure, the code files are grouped by its functional purpose. This structure tells me - all jobs are located here, all controllers are located there and Notifications are in this place.</p>
<p><img class="img-responsive aligncenter wp-image-318 size-full" src="http://lessthan12ms.com/wp-content/uploads/2016/12/2016-12-6o1xm.jpg" width="946" height="427" /></p>
<p>Since I am greatly inspired by <a href="http://lessthan12ms.com/domain-driven-development-ddd-is-a-way-to-design-software-meant-for-growing/">DDD</a> I organize my code in packages which is bounded by some context. For example, all code related to managing users accounts will go to some <code>Account</code> folder and will look like this:</p>
<p>--</p>
<p>So I move from "I have Jobs and Notifications" to "We have these operations that we can execute on user's accounts". See the difference? The developer can see the intents that the previous developer produced.</p>
<p><img class="aligncenter size-full wp-image-321" src="http://lessthan12ms.com/wp-content/uploads/2016/12/2016-12-wwgh1.jpg" alt="" width="1006" height="498" /></p>
<h2>Benefits of DDD-like file organization</h2>
<p>DDD is a development style which says us to separate code pieces by its domain. I like to use it in file structure organizing. DDD-like file structure gives my teams these benefits:</p>
<ul>
	<li>By simply browsing the folders you can clearly see what context this project has, and what pieces of logic each context has (aka what possible account's actions we have). All pieces of code that are related to some context (like Accounts) reside in on place.</li>
	<li>Spend fewer efforts on documentation. The rule that "tests are a documentation of your code" is as valid as "the folder structure is a documentation to your project";</li>
</ul>
<h2>How practically improve Laravel file structure of your next project</h2>
<p>I was inspired by these people who created <a href="https://github.com/lucid-architecture/laravel">Lucid package</a> for Laravel. What the creators tell about it:</p>
<p>The Lucid Architecture is a software architecture that helps contain applications' code bases as they grow to not become overwhelmingly unmaintainable, gets us rid of code rot that will later become legacy code, and translate the day-to-day language such as Feature and Service into actual, physical code.</p>
<p>This is a new approach to organizing files in Laravel projects. I believe there will be more initiatives like this. I think this package will highly improve the output of your team in the long run. It encourages you to organize your code in separated services and operations which are testable, reusable and very explicit to your teammates.</p>
<p>If you want to know more, here is a talk from Abed Halawi, one of the creators of Lucid package:</p>
<p><iframe src="//www.youtube.com/embed/wSnM4JkyxPw" width="560" height="314" allowfullscreen="allowfullscreen"></iframe></p>
<p> </p>
Update:
You may also check this app out: <a href="https://lessthan12ms.com/clean-architecture-implemented-as-a-php-app/">Clean architecture php app</a> and
 look through the source code.