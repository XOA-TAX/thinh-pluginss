<?php
function xoa_text_to_speech_google_api_setting($text) {
    $KEY_API = get_option('xoa_api_key_google', '');
    
    $google_language_code_st = get_option('google_language_code_setting', '');
    $google_voice_name_st = get_option('google_voice_name_setting', '');
    $ssmlGenderr = get_option('ssmlGenderr', '');
    
    $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . $KEY_API;

    $chunks = str_split($text, 5000); 

    $audioContents = [];
    foreach ($chunks as $chunk) {
        // $data = [
        //     'input' => ['text' => $chunk],
        //     'voice' => [
        //         'languageCode' => $google_language_code_st, 
        //         'name' => $google_voice_name_st
        //     ],
        //     'audioConfig' => [
        //         'audioEncoding' => 'LINEAR16',
        //         'effectsProfileId' => ['small-bluetooth-speaker-class-device'],
        //         'pitch' => 0,
        //         'speakingRate' => 1
        //     ]
        // ];

        $data = array(
            'input' => array('text' => $chunk),
            'voice' => array('languageCode' => $google_language_code_st, 'ssmlGender' => $ssmlGenderr, 'name' => $google_voice_name_st),
            'audioConfig' => array('audioEncoding' => 'MP3')
        );

        $json_data = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            error_log('Error accessing Text-to-Speech API: ' . $error);
            curl_close($ch);
            return false;
        }

        $response = json_decode($result, true);
        curl_close($ch);

        if (isset($response['audioContent'])) {
            $audioContents[] = base64_decode($response['audioContent']);
        } else {
            error_log('Error in API response: ' . print_r($response, true));
            return false;
        }
    }
    $finalAudio = implode('', $audioContents);

    return $finalAudio;
}