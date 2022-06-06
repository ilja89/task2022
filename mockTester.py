import requests
import json

url = "http://host.docker.internal/mod/charon/api/tester_callback"

def get_payload(slug, studentSlug):
    return json.dumps({
  "type": "arete",
  "version": "arete_2.0",
  "errors": [
    {
      "message": "D100: Missing docstring in public module",
      "kind": "style error",
      "fileName": "/pytest_tmp/cipher.py",
      "lineNo": 1,
      "columnNo": 0,
      "hint": None
    },
    {
      "message": "D103: Missing docstring in public function",
      "kind": "style error",
      "fileName": "/pytest_tmp/cipher.py",
      "lineNo": 4,
      "columnNo": 0,
      "hint": None
    },
    {
      "message": "D103: Missing docstring in public function",
      "kind": "style error",
      "fileName": "/pytest_tmp/cipher.py",
      "lineNo": 9,
      "columnNo": 0,
      "hint": None
    },
    {
      "message": "D103: Missing docstring in public function",
      "kind": "style error",
      "fileName": "/pytest_tmp/cipher.py",
      "lineNo": 15,
      "columnNo": 0,
      "hint": None
    }
  ],
  "files": [
    {
      "path": "cipher.py",
      "contents": "from itertools import cycle\n\n\ndef rail_pattern(n):\n    r = list(range(n))\n    return cycle(r + r[-2: 0: - 1])\n\n\ndef encode(a, b):\n    p = rail_pattern(b)\n    # this relies on key being called in order, guaranteed?\n    return ''.join(sorted(a, key=lambda i: next(p))).replace(\" \", \"_\")\n\n\ndef decode(a, b):\n    p = rail_pattern(b)\n    indexes = sorted(range(len(a)), key=lambda i: next(p))\n    result = [''] * len(a)\n    for i, c in zip(indexes, a):\n        result[i] = c\n    return ''.join(result).replace(\"_\", \"_\")\n\n\nprint(encode(\"Mind on vaja krüpteerida\", 3))  # => M_v_prido_aaküteiannjred\nprint(encode(\"Mind on\", 3))  # => M_idonn\nprint(encode(\"hello\", 1))  # => hello\nprint(encode(\"hello\", 8))  # => hello\nprint(encode(\"kaks pead\", 1))  # => kaks_pead\n\nprint(decode(\"kaks_pead\", 1))  # => kaks pead\nprint(decode(\"M_idonn\", 3))  # => Mind on\nprint(decode(\"M_v_prido_aaküteiannjred\", 3))  # => Mind on vaja krüpteerida\n"
    }
  ],
  "testFiles": [],
  "testSuites": [
    {
      "unitTests": [
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 5,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_empty_string",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 1,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_character",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_key_1",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_longer_sentence_key_1",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_sentence_key_2",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_sentence_key_3",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_key_bigger_than_length",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_sentence_letter_sensitive",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_sentence_hard",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_sentence_key_bigger_than_length",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_empty_string",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_character",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_key_1",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_key_bigger_than_length",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 1,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_long_text",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "PASSED",
          "weight": 1,
          "printExceptionMessage": False,
          "printStackTrace": None,
          "timeElapsed": 291,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_encode_random",
          "stdout": None,
          "exceptionClass": "",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 1,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_longer_sentence_key_1",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 1,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_sentence_key_2",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 1,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_sentence_key_3",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 0,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_sentence_key_bigger_than_length",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 1,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_sentence_letter_sensitive",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 4,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_long_text",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        },
        {
          "groupsDependedUpon": None,
          "status": "FAILED",
          "weight": 1,
          "printExceptionMessage": True,
          "printStackTrace": None,
          "timeElapsed": 6,
          "methodsDependedUpon": None,
          "stackTrace": None,
          "name": "test_decode_random",
          "stdout": None,
          "exceptionClass": "AssertionError",
          "exceptionMessage": "",
          "stderr": None
        }
      ],
      "name": "/pytest_tmp/cipher_tests.py",
      "file": "/pytest_tmp/cipher_tests.py",
      "startDate": None,
      "endDate": None,
      "weight": None,
      "passedCount": 16,
      "grade": 69.56521739130434
    }
  ],
  "consoleOutputs": "Everything went ok but in Python. Here are some LOGS LOGS LOGS LOGS LOGS ...",
  "output": "<h2>Testing results for envomp</h2><p>Submission hash: 12dacy372642hc3642c3v423xd34v5yb534bn7354</p><br><p>Quote by Charles Swindoll: \"15.Life is 10% what happens to me and 90% of how I react to it.\"</p><br><br><br><table style='width:100%;border: 1px solid black;border-collapse: collapse;' id='errors'><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>File</th><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>Line</th><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>Column</th><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>Error</th></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>cipher.py</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>D100: Missing docstring in public module</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>cipher.py</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>4</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>D103: Missing docstring in public function</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>cipher.py</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>9</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>D103: Missing docstring in public function</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>cipher.py</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>15</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>D103: Missing docstring in public function</td></tr></table><br>Style percentage: 0%<br><br><br><table style='width:100%;border: 1px solid black;border-collapse: collapse;'><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>cipher_tests.py</th><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>Result</th><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>Time (ms)</th><th style='color:#D5DDE5;background:#1b1e24;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>Weight</th><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_empty_string</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>5</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_character</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_key_1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_longer_sentence_key_1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_sentence_key_2</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_sentence_key_3</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_key_bigger_than_length</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_sentence_letter_sensitive</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_sentence_hard</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_sentence_key_bigger_than_length</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_empty_string</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_character</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_key_1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_key_bigger_than_length</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_long_text</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_encode_random</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:greenyellow;'>PASSED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>291</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_longer_sentence_key_1<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_sentence_key_2<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_sentence_key_3<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_sentence_key_bigger_than_length<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>0</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_sentence_letter_sensitive<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_long_text<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>4</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr><tr style='border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>test_decode_random<br><a style='color:red;'>AssertionError</a>:  ... </td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'><p style='color:red;'>FAILED</p></td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>6</td><td style='color:#D5DDE5;background:#393939;border: 1px solid black;border-collapse: collapse;padding: 5px;text-align: left;'>1</td></tr></table><p>Number of tests: 23</p><p>Passed tests: 16</p><p>Total weight: 23</p><p>Passed weight: 16</p><p>Percentage: 69.57%</p><br><br><h2>Overall</h2><p>Total number of tests: 23</p><p>Total passed tests: 16</p><p>Total weight: 23</p><p>Total Passed weight: 16</p><p>Total Percentage: 69.57%</p><br><br><p>Timestamp: 28/05/2020 21:57:33</p>",
  "totalCount": 23,
  "totalGrade": "69.57",
  "totalPassedCount": 16,
  "testingPlatform": "python",
  "root": "iti0102-2019",
  "gitTestRepo": "https://gitlab.cs.ttu.ee/iti0102-2019/ex.git",
  "gitStudentRepo": "https://gitlab.cs.ttu.ee/iljaku/iti0102-2019.git",
  "style": 0,
  "slug": slug,
  "studentSlug": studentSlug,
  "returnExtra": None,
  "hash": "12dacy372642hc3642c3v423xd34v5yb534bn7354",
  "uniid": "iljaku",
  "timestamp": 1590692253276,
  "commitMessage": "First commit!!!",
  "priority": 10,
  "dockerExtra": [
    "stylecheck"
  ],
  "systemExtra": [
    "noMail",
    "minimalFeedback"
  ],
  "dockerTimeout": 120,
  "failed": False,
  "mock": True
})

headers = {
  'Content-Type': 'application/json',
  'Cookie': 'MoodleSession=ss3t77s753b91v69rc5o45r0su'
}

payload = get_payload("illo", "EXAM3")
response = requests.request("POST", url, headers=headers, data=payload)

payload = get_payload("tenetur", "EXAM3")
response = requests.request("POST", url, headers=headers, data=payload)

payload = get_payload("expedita", "EXAM3")
response = requests.request("POST", url, headers=headers, data=payload)

