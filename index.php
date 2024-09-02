<?php
$logFile = 'C:\\Users\\Mihalcea Mathias\\Desktop\\newFile.log';

// 1. Cite accese au fost efectuate in total la serverul web?
function getTotalAccesses($logFile) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        $totalAccesses = 0;

        while (($line = fgets($handle)) !== false) {
            $pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" \d+ (\d+)/';
            preg_match($pattern, $line, $matches);

            if (count($matches) === 6) {
                $method = $matches[3];

                if ($method !== 'HEAD') {
                    $totalAccesses++;
                }
            }
        }

        fclose($handle);

        return $totalAccesses;
    } else {
        return false;
    }
}

// 2. Cite accese au fost OK (cod 200), cite cu ERR (cod 500)?
function getOKAccesses($logFile) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        $okAccesses = 0;

        while (($line = fgets($handle)) !== false) {
            $pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" (200) (\d+)/';
            preg_match($pattern, $line, $matches);

            if (count($matches) === 7) {
                $method = $matches[3];

                if ($method !== 'HEAD') {
                    $okAccesses++;
                }
            }
        }

        fclose($handle);

        return $okAccesses;
    } else {
        return false;
    }
}

function getErrorAccesses($logFile) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        $errorAccesses = 0;

        while (($line = fgets($handle)) !== false) {
            $pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" (500) (\d+)/';
            preg_match($pattern, $line, $matches);

            if (count($matches) === 7) {
                $method = $matches[3];

                if ($method !== 'HEAD') {
                    $errorAccesses++;
                }
            }
        }

        fclose($handle);

        return $errorAccesses;
    } else {
        return false;
    }
}

// 3. Cite accese au fost efectuate in total pe fiecare zi a saptaminii? (luni, marti, ...) Nu i-am dat deloc de cap!
function getAccessesPerDayOfWeek($logFile) {
	$handle = fopen($logFile, 'r');
	if ($handle) {
			$accessesPerDayOfWeek = [
					'Monday' => 0,
					'Tuesday' => 0,
					'Wednesday' => 0,
					'Thursday' => 0,
					'Friday' => 0,
					'Saturday' => 0,
					'Sunday' => 0
			];

			while (($line = fgets($handle)) !== false) {
					$pattern = '/^(\S+) - \S+ \[(\d+\/\w+\/\d+):/';
					preg_match($pattern, $line, $matches);

					if (count($matches) === 3) {
							$date = strtotime($matches[2]);
							$dayOfWeek = date('l', $date);

							$accessesPerDayOfWeek[$dayOfWeek]++;
					}
			}

			fclose($handle);

			return $accessesPerDayOfWeek;
	} else {
			return false;
	}
}

// 4. Care este numarul total de bytes trimisi pt fiecare cod de raspuns? (200, 500, 404, ...)
function getBytesPerResponseCode($logFile) {
	$handle = fopen($logFile, 'r');
	if ($handle) {
			$bytesPerResponseCode = [];

			while (($line = fgets($handle)) !== false) {
					$pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" (\d+) (\d+)/';
					preg_match($pattern, $line, $matches);

					if (count($matches) === 7) {
							$responseCode = $matches[5];
							$bytes = $matches[6];

							if (isset($bytesPerResponseCode[$responseCode])) {
									$bytesPerResponseCode[$responseCode] += $bytes;
							} else {
									$bytesPerResponseCode[$responseCode] = $bytes;
							}
					}
			}

			fclose($handle);

			return $bytesPerResponseCode;
	} else {
			return false;
	}
}

// 5. Ce fel de cereri au fost efectuate si cite din fiecare? (GET, POST, ...)
function getRequestTypes($logFile) {
	$handle = fopen($logFile, 'r');
	if ($handle) {
			$requestTypes = [];

			while (($line = fgets($handle)) !== false) {
					$pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" (\d+) (\d+)/';
					preg_match($pattern, $line, $matches);

					if (count($matches) === 7) {
							$method = $matches[3];

							if ($method !== 'HEAD') {
									if (isset($requestTypes[$method])) {
											$requestTypes[$method]++;
									} else {
											$requestTypes[$method] = 1;
									}
							}
					}
			}

			fclose($handle);

			return $requestTypes;
	} else {
			return false;
	}
}


// 6. Care a fost cea mai accesata pagina web si cu cite accese?
function getMostAccessedPage($logFile) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        $urlAccesses = [];

        while (($line = fgets($handle)) !== false) {
            $pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" (\d+) (\d+)/';
            preg_match($pattern, $line, $matches);

            if (count($matches) === 7) {
                $method = $matches[3];
                $statusCode = $matches[5];
                $url = $matches[4];

                if ($method !== 'HEAD') {
                    if (isset($urlAccesses[$url])) {
                        $urlAccesses[$url]++;
                    } else {
                        $urlAccesses[$url] = 1;
                    }
                }
            }
        }

        fclose($handle);

        if (!empty($urlAccesses)) {
            $mostAccessedURL = '';
            $maxAccesses = 0;

            foreach ($urlAccesses as $url => $accesses) {
                if ($accesses > $maxAccesses) {
                    $maxAccesses = $accesses;
                    $mostAccessedURL = $url;
                }
            }

            return [
                'url' => $mostAccessedURL,
                'accesses' => $maxAccesses
            ];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// 7. Care sint primele 10 adrese IP cu cele mai multe accesari, si cite?
function getTopIPAddresses($logFile, $limit = 10) {
	$handle = fopen($logFile, 'r');
	if ($handle) {
			$ipAccesses = [];

			while (($line = fgets($handle)) !== false) {
					$pattern = '/^(\S+) - \S+ \[(.+)\] "(\S+) (\S+) \S+" (\d+) (\d+)/';
					preg_match($pattern, $line, $matches);

					if (count($matches) === 7) {
							$method = $matches[3];
							$ip = $matches[1];

							if ($method !== 'HEAD') {
									if (isset($ipAccesses[$ip])) {
											$ipAccesses[$ip]++;
									} else {
											$ipAccesses[$ip] = 1;
									}
							}
					}
			}

			fclose($handle);

			arsort($ipAccesses); // Sortăm array-ul descrescător după numărul de accesări

			$topIPs = [];
			$counter = 0;

			foreach ($ipAccesses as $ip => $accesses) {
					$topIPs[$ip] = $accesses;
					$counter++;

					if ($counter === $limit) {
							break;
					}
			}

			return $topIPs;
	} else {
			return false;
	}
}

$totalAccesses = getTotalAccesses($logFile);
$okAccesses = getOKAccesses($logFile);
$errorAccesses = getErrorAccesses($logFile);
// $accessesPerDayOfWeek = getAccessesPerDayOfWeek($logFile);
$bytesPerResponseCode = getBytesPerResponseCode($logFile);
$requestTypes = getRequestTypes($logFile);
$mostAccessedPage = getMostAccessedPage($logFile);
$topIPAddresses = getTopIPAddresses($logFile);


if ($totalAccesses !== false) {
    echo "1. Numărul total de accesări: " . $totalAccesses . "\n";
		echo "\n";
} else {
    echo "Nu s-a putut deschide fișierul log.<br>";
}

if ($okAccesses !== false) {
    echo "2.1. Numărul de accesări OK (cod 200): " . $okAccesses . "\n";
} else {
    echo "Nu s-a putut deschide fișierul log.<br>";
}

if ($errorAccesses !== false) {
    echo "2.2.Numărul de accesări ERR (cod 500): " . $errorAccesses . "\n";
		echo "\n";
} else {
    echo "Nu s-a putut deschide fișierul log.<br>";
}

// if ($accessesPerDayOfWeek !== false) {
//     echo "Numărul total de accesări pe fiecare zi a săptămânii:<br>";
//     foreach ($accessesPerDayOfWeek as $day => $count) {
//         echo $day . ": " . $count . "<br>";
//     }
// } else {
//     echo "Nu s-a putut deschide fișierul log.<br>";
// }

if ($bytesPerResponseCode !== false) {
	echo "4. Numărul total de bytes trimiși pentru fiecare cod de răspuns:" ."\n";
	foreach ($bytesPerResponseCode as $responseCode => $bytes) {
			echo "Codul " . $responseCode . ": " . $bytes . " bytes" . "\n";
	}
	echo "\n";
} else {
	echo "Nu s-a putut deschide fișierul log.<br>";
}

if ($requestTypes !== false) {
	echo "5. Tipurile de cereri efectuate:" . "\n";
	foreach ($requestTypes as $method => $count) {
			echo $method . ": " . $count . "\n";
	}
	echo "\n";
} else {
	echo "Nu s-a putut deschide fișierul log.<br>";
}

if ($mostAccessedPage !== false) {
    echo "6. Cea mai accesată pagină web: " . $mostAccessedPage['url'] . "\n";
    echo "Numărul de accesări ale acestei pagini: " . $mostAccessedPage['accesses'] . "\n";
		echo "\n";
} else {
    echo "Nu s-au găsit accesări pentru adrese URL.<br>";
}

if ($topIPAddresses !== false) {
	echo "7. Cele mai accesate adrese IP:" . "\n";
	foreach ($topIPAddresses as $ip => $accesses) {
			echo $ip . ": " . $accesses . " accesări" . "\n";
	}
	echo "\n";
} else {
	echo "Nu s-a putut deschide fișierul log.<br>";
}




?>
