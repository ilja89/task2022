<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

//Receive the RAW post data via the php://input IO stream.
$content = file_get_contents("php://input");

$json_input = json_decode($content);

ob_start();
var_dump($json_input);
$result = ob_get_clean();

$myfile = fopen("/tmp/callback_log.txt", "a") or die("Unable to open file!");
fwrite($myfile, $content);
fwrite($myfile, $result);
fwrite($myfile, "\n\n------\n\n");
fclose($myfile);

$json_result = '
{
   "version":"2.0, build 20190430_165148",
   "type":"hodor_studenttester",
   "contentRoot":"/host/source",
   "testRoot":"/host/tests",
   "results":[
      {
         "code":103,
         "files":[
            {
               "path":"SumString.rst",
               "contents":"text here",
               "isTest":true
            },
            {
               "path":"src/ExerciseTest.java",
               "contents":"import ee.ttu.java.studenttester.annotations.Gradable;\nimport ee.ttu.java.studenttester.annotations.TestContextConfiguration;\nimport ee.ttu.java.studenttester.enums.ReportMode;\nimport org.testng.Assert;\nimport org.testng.annotations.Test;\n\nimport java.util.Random;\n\n@TestContextConfiguration(mode = ReportMode.MAXVERBOSE)\npublic class ExerciseTest {\n\n    @Test(timeOut = 1000)\n    @Gradable(description = \"\")\n    public void testExamples() throws Exception {\n        Assert.assertEquals(Exercise.sumString(2, 3), \"2 + 3 = 5\");\n        Assert.assertEquals(Exercise.sumString(12, 34), \"12+ 34 = 46\");\n        Assert.assertEquals(Exercise.sumString(0, 0), \"0 + 0 = 0\");\n    }\n\n    @Test(timeOut = 1000)\n    @Gradable(description = \"\")\n    public void testDifferentStuff() throws Exception {\n        Assert.assertEquals(Exercise.sumString(0, 1), \"0 + 1 = 1\");\n        Assert.assertEquals(Exercise.sumString(1, 0), \"1 + 0 = 1\");\n        Assert.assertEquals(Exercise.sumString(100, 0), \"100 + 0 = 100\");\n        Assert.assertEquals(Exercise.sumString(999, 1), \"999 + 1 = 1000\");\n        Assert.assertEquals(Exercise.sumString(1, 999), \"1 + 999 = 1000\");\n        Assert.assertEquals(Exercise.sumString(-1, 999), \"-1 + 999 = 998\");\n        Assert.assertEquals(Exercise.sumString(-1, -1), \"-1 + -1 = -2\");\n    }\n\n    @Test(timeOut = 1000)\n    @Gradable(description = \"\")\n    public void testRandom() throws Exception {\n        Random rnd = new Random();\n        for (int i = 0; i < 1000; i++) {\n      int a = rnd.nextInt(1000);\n            int b = rnd.nextInt(1000);\n            int c = a + b;\n            Assert.assertEquals(Exercise.sumString(a, b), \"\" + a + \" + \" + b + \" = \" + c);\n        }\n    }\n\n}\n",
               "isTest":true
            },
            {
               "path":"src/Exercise.java",
               "contents":"public class Exercise {\n\n    /**\n     * Given two numbers a and b returns a string in format:\n     * a + b = c\n     * where c is sum of a and b.\n     * <p>\n     * sumString(1, 2) => \"1 + 2 = 3\"\n     * sumString(12, 34) => \"12 + 34 = 46\"\n     * sumString(0, 0) => \"0 + 0 = 0\"\n     *\n     * @param a\n     * @param b\n     * @return\n     */\n    public static String sumString(int a, int b) {\n        return \"1 +2 = 3\";\n    }\n\n}\n",
               "isTest":false
            }
],
         "identifier":"FILEWRITER",
         "result":"SUCCESS"
    },
      {
         "code":102,
         "diagnosticList":[

         ],
         "identifier":"COMPILER",
         "result":"SUCCESS"
      },
      {
         "code":500,
         "identifier":"TESTNG",
         "result":"SUCCESS",
         "securityViolation":false,
         "testContexts":[
            {
               "unitTests":[
                  {
                     "status":"FAILED",
                     "weight":1,
                     "description":"",
                     "printExceptionMessage":false,
                     "printStackTrace":false,
                     "timeElapsed":43,
                     "groupsDependedUpon":[

                     ],
                     "methodsDependedUpon":[

                     ],
                     "name":"testDifferentStuff",
                     "stackTrace":"java.lang.AssertionError: expected [0 + 1 = 1] but found [1 + 2 = 3]\n\tat org.testng.Assert.fail(Assert.java:96)\n\tat org.testng.Assert.failNotEquals(Assert.java:776)\n\tat org.testng.Assert.assertEqualsImpl(Assert.java:137)\n\tat org.testng.Assert.assertEquals(Assert.java:118)\n\tat org.testng.Assert.assertEquals(Assert.java:453)\n\tat org.testng.Assert.assertEquals(Assert.java:463)\n\tat ExerciseTest.testDifferentStuff(ExerciseTest.java:23)\n\tat java.base/jdk.internal.reflect.NativeMethodAccessorImpl.invoke0(Native Method)\n\tat java.base/jdk.internal.reflect.NativeMethodAccessorImpl.invoke(NativeMethodAccessorImpl.java:62)\n\tat java.base/jdk.internal.reflect.DelegatingMethodAccessorImpl.invoke(DelegatingMethodAccessorImpl.java:43)\n\tat java.base/java.lang.reflect.Method.invoke(Method.java:566)\n\tat org.testng.internal.MethodInvocationHelper.invokeMethod(MethodInvocationHelper.java:124)\n\tat org.testng.internal.InvokeMethodRunnable.runOne(InvokeMethodRunnable.java:54)\n\tat org.testng.internal.InvokeMethodRunnable.run(InvokeMethodRunnable.java:44)\n\tat java.base/java.util.concurrent.Executors$RunnableAdapter.call(Executors.java:515)\n\tat java.base/java.util.concurrent.FutureTask.run(FutureTask.java:264)\n\tat java.base/java.util.concurrent.ThreadPoolExecutor.runWorker(ThreadPoolExecutor.java:1128)\n\tat java.base/java.util.concurrent.ThreadPoolExecutor$Worker.run(ThreadPoolExecutor.java:628)\n\tat java.base/java.lang.Thread.run(Thread.java:834)\n",
                     "exceptionClass":"java.lang.AssertionError",
                     "exceptionMessage":"expected [0 + 1 = 1] but found [1 + 2 = 3]",
                     "stdout":[

                     ],
                     "stderr":[

                     ]
                  },
{
                     "status":"FAILED",
                     "weight":1,
                     "description":"",
                     "printExceptionMessage":false,
                     "printStackTrace":false,
                     "timeElapsed":3,
                     "groupsDependedUpon":[

                     ],
                     "methodsDependedUpon":[

                     ],
                     "name":"testExamples",
                     "stackTrace":"java.lang.AssertionError: expected [2 + 3 = 5] but found [1 + 2 = 3]\n\tat org.testng.Assert.fail(Assert.java:96)\n\tat org.testng.Assert.failNotEquals(Assert.java:776)\n\tat org.testng.Assert.assertEqualsImpl(Assert.java:137)\n\tat org.testng.Assert.assertEquals(Assert.java:118)\n\tat org.testng.Assert.assertEquals(Assert.java:453)\n\tat org.testng.Assert.assertEquals(Assert.java:463)\n\tat ExerciseTest.testExamples(ExerciseTest.java:15)\n\tat java.base/jdk.internal.reflect.NativeMethodAccessorImpl.invoke0(Native Method)\n\tat java.base/jdk.internal.reflect.NativeMethodAccessorImpl.invoke(NativeMethodAccessorImpl.java:62)\n\tat java.base/jdk.internal.reflect.DelegatingMethodAccessorImpl.invoke(DelegatingMethodAccessorImpl.java:43)\n\tat java.base/java.lang.reflect.Method.invoke(Method.java:566)\n\tat org.testng.internal.MethodInvocationHelper.invokeMethod(MethodInvocationHelper.java:124)\n\tat org.testng.internal.InvokeMethodRunnable.runOne(InvokeMethodRunnable.java:54)\n\tat org.testng.internal.InvokeMethodRunnable.run(InvokeMethodRunnable.java:44)\n\tat java.base/java.util.concurrent.Executors$RunnableAdapter.call(Executors.java:515)\n\tat java.base/java.util.concurrent.FutureTask.run(FutureTask.java:264)\n\tat java.base/java.util.concurrent.ThreadPoolExecutor.runWorker(ThreadPoolExecutor.java:1128)\n\tat java.base/java.util.concurrent.ThreadPoolExecutor$Worker.run(ThreadPoolExecutor.java:628)\n\tat java.base/java.lang.Thread.run(Thread.java:834)\n",
                     "exceptionClass":"java.lang.AssertionError",
                     "exceptionMessage":"expected [2 + 3 = 5] but found [1 + 2 = 3]",
                     "stdout":[

                     ],
                     "stderr":[

                     ]
                  },
{
                     "status":"FAILED",
                     "weight":1,
                     "description":"",
                     "printExceptionMessage":false,
                     "printStackTrace":false,
                     "timeElapsed":45,
                     "groupsDependedUpon":[

                     ],
                     "methodsDependedUpon":[

                     ],
                     "name":"testRandom",
                     "stackTrace":"java.lang.AssertionError: expected [197 + 717 = 914] but found [1 + 2 = 3]\n\tat org.testng.Assert.fail(Assert.java:96)\n\tat org.testng.Assert.failNotEquals(Assert.java:776)\n\tat org.testng.Assert.assertEqualsImpl(Assert.java:137)\n\tat org.testng.Assert.assertEquals(Assert.java:118)\n\tat org.testng.Assert.assertEquals(Assert.java:453)\n\tat org.testng.Assert.assertEquals(Assert.java:463)\n\tat ExerciseTest.testRandom(ExerciseTest.java:40)\n\tat java.base/jdk.internal.reflect.NativeMethodAccessorImpl.invoke0(Native Method)\n\tat java.base/jdk.internal.reflect.NativeMethodAccessorImpl.invoke(NativeMethodAccessorImpl.java:62)\n\tat java.base/jdk.internal.reflect.DelegatingMethodAccessorImpl.invoke(DelegatingMethodAccessorImpl.java:43)\n\tat java.base/java.lang.reflect.Method.invoke(Method.java:566)\n\tat org.testng.internal.MethodInvocationHelper.invokeMethod(MethodInvocationHelper.java:124)\n\tat org.testng.internal.InvokeMethodRunnable.runOne(InvokeMethodRunnable.java:54)\n\tat org.testng.internal.InvokeMethodRunnable.run(InvokeMethodRunnable.java:44)\n\tat java.base/java.util.concurrent.Executors$RunnableAdapter.call(Executors.java:515)\n\tat java.base/java.util.concurrent.FutureTask.run(FutureTask.java:264)\n\tat java.base/java.util.concurrent.ThreadPoolExecutor.runWorker(ThreadPoolExecutor.java:1128)\n\tat java.base/java.util.concurrent.ThreadPoolExecutor$Worker.run(ThreadPoolExecutor.java:628)\n\tat java.base/java.lang.Thread.run(Thread.java:834)\n",
                     "exceptionClass":"java.lang.AssertionError",
                     "exceptionMessage":"expected [197 + 717 = 914] but found [1 + 2 = 3]",
                     "stdout":[

                     ],
                     "stderr":[

                     ]
                  }
               ],
               "name":"ExerciseTest (TestNG)",
               "file":"ExerciseTest",
               "startDate":1563950053870,
               "endDate":1563950056735,
               "mode":"MAXVERBOSE",
               "welcomeMessage":"",
               "identifier":1,
               "count":3,
               "weight":3,
               "passedCount":0,
               "grade":0.0
            }
         ],
         "totalCount":3,
         "totalGrade":0.0,
         "totalPassedCount":0
      },
{
         "code":2147483647,
         "identifier":"REPORT",
         "output":"TEST RESULTS\n\n\n\n* Compiler report *\n\nCompilation succeeded.\n\n* Unit tests *\n\n\nExerciseTest (TestNG)\nWed Jul 24 06:34:16 UTC 2019\n ---\nFAILURE: testDifferentStuff\n\t43 msecs, weight: 1 unit\n\tException type: java.lang.AssertionError\n\tDetailed information:  expected [0 + 1 = 1] but found [1 + 2 = 3]\n\tStack trace:  java.lang.AssertionError: expected [0 + 1 = 1] but found [1 + 2 = 3]\n\nPassed unit tests: 0/3\nFailed unit tests: 3\nSkipped unit tests: 0\nGrade: 0.0%\n\nOverall grade: 0.0%\n",
         "result":"SUCCESS"
      }

]
}
';

$callback_url = $json_input->callback_url;
$callback_secret = $json_input->secret_token;
$callback_uniid = $json_input->user;
$callback_project = 'hello';
if (@$json_input->project) {
    $callback_project = $json_input->project;
}
//API Url
$url = $callback_url;

//Initiate cURL.
$ch = curl_init($url);

$json_data = json_decode($json_result);
$last_error = json_last_error();
if ($last_error > 0) {
    var_dump($last_error);
    var_dump(json_last_error_msg());
}

$json_data->token = $callback_secret;
$json_data->uniid = $callback_uniid;
$json_data->project = $callback_project;

//Encode the array into JSON.
$json_data_encoded = json_encode($json_data);

//Tell cURL that we want to send a POST request.
curl_setopt($ch, CURLOPT_POST, 1);

//Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data_encoded);

//Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

//Execute the request
$result = curl_exec($ch);
