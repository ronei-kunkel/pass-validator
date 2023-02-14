<?php

namespace PassValidator\Controller;

use PassValidator\Http\Request;

class VerifyController {

  /**
   * Valid rules to verify the password
   *
   * @var array
   */
  private static $availableRules = [
    'minSize',
    'minUppercase',
    'minLowercase',
    'minDigit',
    'minSpecialChars',
    'noRepeted'
  ];

  /**
   * Request to use in class context
   *
   * @var Request
   */
  private static $request;

  /**
   * Verify and send response of request about the password to be validated
   *
   * @param Request $request
   * @return void
   */
  public static function verify(Request $request) {
    self::$request = $request;

    $body = $request->getBody();

    self::validateBody($body);

    $violatedRules = self::verifyViolatedRules($body);

    $verify = empty($violatedRules);

    $response = [
      'verify' => $verify,
      'noMatch' => $violatedRules
    ];

    $request->sendResponse($response, 200);
  }

  /**
   * Validate the client body input and send response if have erorrs on received data
   *
   * @param array $body
   * @return void
   */
  private static function validateBody($body) {
    $errors = [];
    $error = [];
    if (!isset($body['password']))                                  $error['password'][] = "Field is mandatory.";
    if (isset($body['password']) and !is_string($body['password'])) $error['password'][] = "Field should be a string.";
    if (isset($body['password']) and is_null($body['password']))    $error['password'][] = "Field should not be null.";
    if (isset($body['password']) and empty($body['password']))      $error['password'][] = "Field should not be empty.";
    if (!isset($body['rules']))                                     $error['rules'][]    = "Field is mandatory.";
    if (isset($body['rules']) and !is_array($body['rules']))        $error['rules'][]    = "Field should be an array.";
    if (isset($body['rules']) and empty($body['rules']))            $error['rules'][]    = "Field should not be empty.";

    foreach ($body['rules'] as $key => $rule) {
      if(!isset($rule['rule']))                                                                                     $error['rules']['rule']["$key."]['rule'][]  = "Field is mandatory.";
      if(isset($rule['rule']) and preg_match_all('/\s|\d|\W|\_/', $rule['rule']) > 0)                               $error['rules']['rule']["$key."]['rule'][]  = "Field must contain only letters.";
      if(isset($rule['rule']) and !in_array($rule['rule'], self::$availableRules))                                  $error['rules']['rule']["$key."]['rule'][]  = "The rule '" . $rule['rule'] . "' is not valid.";
      if($rule['rule'] != 'noRepeted' and !isset($rule['value']))                                                   $error['rules']['rule']["$key."]['value'][] = "Field is mandatory.";
      if($rule['rule'] != 'noRepeted' and isset($rule['value']) and preg_match_all('/^\d+$/', $rule['value']) == 0) $error['rules']['rule']["$key."]['value'][] = "Field should be positive integer.";
    }

    $errors['error'] = $error;

    if(!empty($errors['error'])) self::$request->sendResponse($error, 400);
  }

  /**
   * Verify what rules are not followed by password
   *
   * @param array $body
   * @return array
   */
  private static function verifyViolatedRules(array $body) {
    $mapedRules = self::mapRuleValues($body);

    if(empty($mapedRules)) return $mapedRules;

    $followedRules = self::followedRules($body['password'], $mapedRules);

    return self::checkViolatedRules($followedRules);
  }

  /**
   * Map the rules with respective values
   *
   * @param array $body
   * @return array
   */
  private static function mapRuleValues($body) {
    $rules = [];
    if(!isset($body['rules']) or is_null($body['rules']) or empty($body['rules'])) return $rules;
    foreach ($body['rules'] as $key => $rule) {
      $rules[$rule['rule']] = $rule['value'] ?? null;
    }
    return $rules;
  }

  /**
   * Apply rules into password received from client and return map wit followed or unfollowed rules
   *
   * @param string $password
   * @param array $rules
   * @return array
   */
  private static function followedRules($password, $rules) {
    $followedRules = [];
    foreach ($rules as $rule => $value) {

      if (!in_array($rule, self::$availableRules)) continue;

      if (method_exists(self::class, $rule)) $followedRules[$rule] = self::$rule($password, $value);
    }
    return $followedRules;
  }

  /**
   * Verify size of password
   *
   * @param string $password
   * @param integer $valueValidate
   * @return bool
   */
  private static function minSize(string $password, int $valueValidate) {
    return (strlen($password) >= $valueValidate) ? true : false;
  }

  /**
   * Verify minimum quantity of uppercase characteres
   *
   * @param string $password
   * @param integer $valueValidate
   * @return bool
   */
  private static function minUppercase(string $password, int $valueValidate) {
    $passwordRuleValue = preg_match_all("/[A-Z]/", $password);

    return ($passwordRuleValue >= $valueValidate) ? true : false;
  }

  /**
   * Verify minimum quantity of lowercase characteres
   *
   * @param string $password
   * @param integer $valueValidate
   * @return bool
   */
  private static function minLowercase(string $password, int $valueValidate) {
    $passwordRuleValue = preg_match_all("/[a-z]/", $password);

    return ($passwordRuleValue >= $valueValidate) ? true : false;
  }

  /**
   * Verify minimum quantity of numeric digits
   *
   * @param string $password
   * @param integer $valueValidate
   * @return bool
   */
  private static function minDigit(string $password, int $valueValidate) {
    $passwordRuleValue = preg_match_all("/[0-9]/", $password);

    return ($passwordRuleValue >= $valueValidate) ? true : false;
  }

  /**
   * Verify minimum quantity of special characteres
   *
   * @param string $password
   * @param integer $valueValidate
   * @return void
   */
  private static function minSpecialChars(string $password, int $valueValidate) {
    $passwordRuleValue = preg_match_all('/[\!\@\#\$\%\^\&\*\(\)\-\+\{\}\[\]\\/\\\\]/', $password);

    return ($passwordRuleValue >= $valueValidate) ? true : false;
  }

  /**
   * Verify if have any repeted character in password
   *
   * @param string $password
   * @return bool
   */
  private static function noRepeted(string $password) {
    $passwordRuleValue = preg_match_all('/(.)\1{1,}/', $password);

    return ($passwordRuleValue > 0) ? false : true;
  }

  /**
   * Return violated rules
   *
   * @param array $appliedRules
   * @return array
   */
  private static function checkViolatedRules($appliedRules) {
    foreach ($appliedRules as $rule => $checked) {
      if ($checked) unset($appliedRules[$rule]);
    }

    return array_keys($appliedRules);
  }
}
