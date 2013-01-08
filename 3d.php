<?php
	/*
	* Copyright (c) 2012, Pierre Gros
	* All rights reserved.
	* Redistribution and use in source and binary forms, with or without
	* modification, are permitted provided that the following conditions are met:
	*
	*     * Redistributions of source code must retain the above copyright
	*       notice, this list of conditions and the following disclaimer.
	*     * Redistributions in binary form must reproduce the above copyright
	*       notice, this list of conditions and the following disclaimer in the
	*       documentation and/or other materials provided with the distribution.
	*     * The names of its contributors may not be used to endorse or promote products
	*       derived from this software without specific prior written permission.
	*
	* THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY
	* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	* DISCLAIMED. IN NO EVENT SHALL THE CONTRIBUTORS BE LIABLE FOR ANY
	* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
	 */
	/*
	 * Developped by Pierre Gros
	 * april, 17 2012
	 * 
	 * login = minecraft login for the skin
	 * a 	= vertical rotation
	 * w 	= horizontal rotation
	 * 
	 * wt 	= horizontal rotation of the head
	 * 
	 * ajg	= vertical rotation of the left leg
	 * ajd	= vertical rotation of the right leg
	 * abg	= vertical rotation of the left arm
	 * abd	= vertical rotation of the right arm
	 * 
	 * displayHairs	= set to "false" not to display hairs
	 * headOnly		= set to "true" to display only the head (and the hair, depending on the "displayHairs" input
	 * 
	 * format 	= "png" by default, set to "svg" to have a vectorial version of the skin
	 * ratio	= used only if "png" format is used. the default (and minimum) value is 2. it represent the number of times the skin will be enlarged
	 */
	error_reporting(E_ERROR);
	
	$seconds_to_cache = 60*60*24*7;//duration of the cache sent to the browser
	
	
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	$times = array(array('Start', microtime_float()));
	$login = $_GET['login'];
	if(trim($login) == '')
		$imgPng = imageCreateFromPng('tmp.png');
	else
		$imgPng = imageCreateFromPng('http://s3.amazonaws.com/MinecraftSkins/'.$login.'.png');
	
	if(!$imgPng)
        	$imgPng = imageCreateFromPng('tmp.png');
        
	imageAlphaBlending($imgPng, true);
	imageSaveAlpha($imgPng, true);
	
	$times[] = array('Telechargement-Image', microtime_float());
	
	$a = $_GET['a'];
	$w = $_GET['w'];
	
	$headOnly = ($_GET['headOnly']=='true');
	$displayHairs = ($_GET['displayHairs']!='false');
	
	$alpha = deg2rad($a);//autour de x
	$omega = deg2rad($w);//autour de y
	
	//ceux là il faut les garder, ils sont utilisés en global un peu partout.
	$ca = cos($alpha);
	$sa = sin($alpha);
	$cw = cos($omega);
	$sw = sin($omega);
	
	
	$anglesMembres = array();
	
	$anglesMembres['torse'] = array('ca' => cos(0), 'sa' => sin(0), 'cw' => cos(0), 'sw' => sin(0));
	/*'tete' 
	'torse'
	'brasD' 
	'brasG'
	'jambeD' 
	'jambeG'*/
	
	$aplhaTete = 0;//deg2rad($_GET['at']);//corriger un jour peut être...
	$omegaTete = deg2rad($_GET['wt']);
	$anglesMembres['tete'] = array('ca' => cos($aplhaTete), 'sa' => sin($aplhaTete), 'cw' => cos($omegaTete), 'sw' => sin($omegaTete));
	$anglesMembres['casque'] = array('ca' => cos($aplhaTete), 'sa' => sin($aplhaTete), 'cw' => cos($omegaTete), 'sw' => sin($omegaTete));
	
	$aplhaBrasG = deg2rad($_GET['abd']);
	$omegaBrasG = 0;//deg2rad($_GET['wt']);//corriger un jour peut être...
	$anglesMembres['brasD'] = array('ca' => cos($aplhaBrasG), 'sa' => sin($aplhaBrasG), 'cw' => cos($omegaBrasG), 'sw' => sin($omegaBrasG));
	
	$aplhaBrasD = deg2rad($_GET['abg']);
	$omegaBrasD = 0;//deg2rad($_GET['wbd']);//corriger un jour peut être...
	$anglesMembres['brasG'] = array('ca' => cos($aplhaBrasD), 'sa' => sin($aplhaBrasD), 'cw' => cos($omegaBrasD), 'sw' => sin($omegaBrasD));
	
	$aplhaJambeG = deg2rad($_GET['ajd']);
	$omegaJambeG = 0;//deg2rad($_GET['wt']);//corriger un jour peut être...
	$anglesMembres['jambeD'] = array('ca' => cos($aplhaJambeG), 'sa' => sin($aplhaJambeG), 'cw' => cos($omegaJambeG), 'sw' => sin($omegaJambeG));
	
	$aplhaJambeD = deg2rad($_GET['ajg']);
	$omegaJambeD = 0;//deg2rad($_GET['wbd']);//corriger un jour peut être...
	$anglesMembres['jambeG'] = array('ca' => cos($aplhaJambeD), 'sa' => sin($aplhaJambeD), 'cw' => cos($omegaJambeD), 'sw' => sin($omegaJambeD));
	
	$minX = 0;
	$maxX = 0;
	$minY = 0;
	$maxY = 0;
	
	
	$times[] = array('Calculs-Angles', microtime_float());
	
	$faceVisiblesFormat = array('avant' => array(), 'arriere' => array());
	$facesVisibles = array(
			'tete' => $faceVisiblesFormat, 
			'torse' => $faceVisiblesFormat, 
			'brasD' => $faceVisiblesFormat, 
			'brasG' => $faceVisiblesFormat, 
			'jambeD' => $faceVisiblesFormat, 
			'jambeG' => $faceVisiblesFormat);
	
	//TODO : boucler sur chacun, preProject & Project
	//Puis calcul des faces visibles pour chacun
	//et le faire aussi au niveau de l'affichage
	$toutesFaces = array('arriere',   'droite', 'haut', 'avant',     'gauche', 'bas');
	foreach  ($facesVisibles as $k => &$v)
	{
		unset($cubeMaxDepthFaces, $cubePoints);
		$cubePoints = array();
		$cubePoints[] = array(new Point(array('x' => 0,'y' => 0,'z' => 0)), array('arriere',   'droite', 'haut'));//0
		$cubePoints[] = array(new Point(array('x' => 0,'y' => 0,'z' => 1)), array('avant',     'droite', 'haut'));//1
		$cubePoints[] = array(new Point(array('x' => 0,'y' => 1,'z' => 0)), array('arriere',   'droite', 'bas'));//2
		$cubePoints[] = array(new Point(array('x' => 0,'y' => 1,'z' => 1)), array('avant',     'droite', 'bas'));//3
		$cubePoints[] = array(new Point(array('x' => 1,'y' => 0,'z' => 0)), array('arriere',   'gauche', 'haut'));//4
		$cubePoints[] = array(new Point(array('x' => 1,'y' => 0,'z' => 1)), array('avant',     'gauche', 'haut'));//5
		$cubePoints[] = array(new Point(array('x' => 1,'y' => 1,'z' => 0)), array('arriere',   'gauche', 'bas'));//6
		$cubePoints[] = array(new Point(array('x' => 1,'y' => 1,'z' => 1)), array('avant',     'gauche', 'bas'));//7
		foreach($cubePoints as $cubePoint)
		{
			
			$cubePoint[0]->preProject(0, 0, 0, $anglesMembres[$k]['ca'], $anglesMembres[$k]['sa'], $anglesMembres[$k]['cw'], $anglesMembres[$k]['sw']);
			
			$cubePoint[0]->project();
			
			if(!isset($cubeMaxDepthFaces))
				$cubeMaxDepthFaces = $cubePoint;
			elseif ($cubeMaxDepthFaces[0]->getDepth() > $cubePoint[0]->getDepth())
			{
				$cubeMaxDepthFaces = $cubePoint;
			}
		}
		$v['arriere'] = $cubeMaxDepthFaces[1];
		$v['avant'] = array_diff($toutesFaces, $v['arriere']);
	}
	
	
	$cubePoints = array();
	$cubePoints[] = array(new Point(array('x' => 0,'y' => 0,'z' => 0)), array('arriere',   'droite', 'haut'));//0
	$cubePoints[] = array(new Point(array('x' => 0,'y' => 0,'z' => 1)), array('avant',     'droite', 'haut'));//1
	$cubePoints[] = array(new Point(array('x' => 0,'y' => 1,'z' => 0)), array('arriere',   'droite', 'bas'));//2
	$cubePoints[] = array(new Point(array('x' => 0,'y' => 1,'z' => 1)), array('avant',     'droite', 'bas'));//3
	$cubePoints[] = array(new Point(array('x' => 1,'y' => 0,'z' => 0)), array('arriere',   'gauche', 'haut'));//4
	$cubePoints[] = array(new Point(array('x' => 1,'y' => 0,'z' => 1)), array('avant',     'gauche', 'haut'));//5
	$cubePoints[] = array(new Point(array('x' => 1,'y' => 1,'z' => 0)), array('arriere',   'gauche', 'bas'));//6
	$cubePoints[] = array(new Point(array('x' => 1,'y' => 1,'z' => 1)), array('avant',     'gauche', 'bas'));//7
	
	unset($cubeMaxDepthFaces);
	foreach($cubePoints as $cubePoint)
	{
		$cubePoint[0]->project();
		
		if(!isset($cubeMaxDepthFaces))
			$cubeMaxDepthFaces = $cubePoint;
		elseif ($cubeMaxDepthFaces[0]->getDepth() > $cubePoint[0]->getDepth())
		{
			$cubeMaxDepthFaces = $cubePoint;
		}
	}
	
	
	$facesArriere = $cubeMaxDepthFaces[1];
	$facesAvant = array_diff($toutesFaces, $facesArriere);
	
	
	$times[] = array('Determination-des-faces', microtime_float());
	
	/*$cubePoints = array();
	$cubePoints[] = new Point(array('x' => 0,'y' => 0,'z' => 0));//0
	$cubePoints[] = new Point(array('x' => 0,'y' => 0,'z' => 1));//1
	$cubePoints[] = new Point(array('x' => 0,'y' => 1,'z' => 0));//2
	$cubePoints[] = new Point(array('x' => 0,'y' => 1,'z' => 1));//3
	$cubePoints[] = new Point(array('x' => 1,'y' => 0,'z' => 0));//4
	$cubePoints[] = new Point(array('x' => 1,'y' => 0,'z' => 1));//5
	$cubePoints[] = new Point(array('x' => 1,'y' => 1,'z' => 0));//6
	$cubePoints[] = new Point(array('x' => 1,'y' => 1,'z' => 1));//7
	*/
	/*
	$polygones = array();
	$polygones[] = new Polygon(array($cubePoints[1], $cubePoints[5], $cubePoints[7], $cubePoints[3]), imagecolorat($imgPng, 0, 0));
	$polygones[] = new Polygon(array($cubePoints[0], $cubePoints[1], $cubePoints[3], $cubePoints[2]), imagecolorat($imgPng, 1, 0));
	$polygones[] = new Polygon(array($cubePoints[0], $cubePoints[1], $cubePoints[5], $cubePoints[4]), imagecolorat($imgPng, 2, 0));
	$polygones[] = new Polygon(array($cubePoints[4], $cubePoints[5], $cubePoints[7], $cubePoints[6]), imagecolorat($imgPng, 3, 0));
	$polygones[] = new Polygon(array($cubePoints[2], $cubePoints[6], $cubePoints[7], $cubePoints[3]), imagecolorat($imgPng, 4, 0));
	$polygones[] = new Polygon(array($cubePoints[0], $cubePoints[4], $cubePoints[6], $cubePoints[2]), imagecolorat($imgPng, 5, 0));
	*/
	
	$faceDepths = array();
	
	$polygones = array();
	
	$arrayFacesCube = array('avant' => array(), 
				'arriere' => array(), 
				'haut' => array(), 
				'bas' => array(), 
				'droite' => array(), 
				'gauche' => array());
	$polygones = array('casque' => $arrayFacesCube, 
			'tete' => $arrayFacesCube, 
			'torse' => $arrayFacesCube, 
			'brasD' => $arrayFacesCube, 
			'brasG' => $arrayFacesCube, 
			'jambeD' => $arrayFacesCube, 
			'jambeG' => $arrayFacesCube);
	
	//Tête
	for($i=0;$i<9;$i++)
	{
		for($j=0;$j<9;$j++)
		{
			if(!isset($volumePoints[$i][$j][-2]))
			{
				$volumePoints[$i][$j][-2] = new Point(array('x' => $i,'y' => $j,'z' => -2));
			}
			if(!isset($volumePoints[$i][$j][6]))
			{
				$volumePoints[$i][$j][6] = new Point(array('x' => $i,'y' => $j,'z' => 6));
			}
		}
	}
	for($j=0;$j<9;$j++)
	{
		for($k=-2;$k<7;$k++)
		{
			if(!isset($volumePoints[0][$j][$k]))
			{
				$volumePoints[0][$j][$k] = new Point(array('x' => 0,'y' => $j,'z' => $k));
			}if(!isset($volumePoints[8][$j][$k]))
			{
				$volumePoints[8][$j][$k] = new Point(array('x' => 8,'y' => $j,'z' => $k));
			}
		}
	}
	for($i=0;$i<9;$i++)
	{
		for($k=-2;$k<7;$k++)
		{
			if(!isset($volumePoints[$i][0][$k]))
			{
				$volumePoints[$i][0][$k] = new Point(array('x' => $i,'y' => 0,'z' => $k));
			}
			if(!isset($volumePoints[$i][8][$k]))
			{
				$volumePoints[$i][8][$k] = new Point(array('x' => $i,'y' => 8,'z' => $k));
			}
		}
	}
	
	for($i=0;$i<8;$i++)
	{
		for($j=0;$j<8;$j++)
		{
			$polygones['tete']['arriere'][] = new Polygon(
				array(
					$volumePoints[$i][$j][-2], 
					$volumePoints[$i+1][$j][-2], 
					$volumePoints[$i+1][$j+1][-2], 
					$volumePoints[$i][$j+1][-2]), 
				imagecolorat($imgPng, 31-$i, 8+$j));
			$polygones['tete']['avant'][] = new Polygon(
				array(
					$volumePoints[$i][$j][6], 
					$volumePoints[$i+1][$j][6], 
					$volumePoints[$i+1][$j+1][6], 
					$volumePoints[$i][$j+1][6]), 
				imagecolorat($imgPng, 8+$i, 8+$j));
		}
	}
	for($j=0;$j<8;$j++)
	{
		for($k=-2;$k<6;$k++)
		{
			$polygones['tete']['droite'][] = new Polygon(
				array(
					$volumePoints[0][$j][$k], 
					$volumePoints[0][$j][$k+1], 
					$volumePoints[0][$j+1][$k+1], 
					$volumePoints[0][$j+1][$k]), 
				imagecolorat($imgPng, $k+2, 8+$j));
			$polygones['tete']['gauche'][] = new Polygon(
				array(
					$volumePoints[8][$j][$k], 
					$volumePoints[8][$j][$k+1], 
					$volumePoints[8][$j+1][$k+1], 
					$volumePoints[8][$j+1][$k]), 
				imagecolorat($imgPng, 23-$k-2, 8+$j));
		}
	}
	for($i=0;$i<8;$i++)
	{
		for($k=-2;$k<6;$k++)
		{
			$polygones['tete']['haut'][] = new Polygon(
				array(
					$volumePoints[$i][0][$k], 
					$volumePoints[$i+1][0][$k], 
					$volumePoints[$i+1][0][$k+1], 
					$volumePoints[$i][0][$k+1]), 
				imagecolorat($imgPng, 8+$i, $k+2));
			$polygones['tete']['bas'][] = new Polygon(
				array(
					$volumePoints[$i][8][$k], 
					$volumePoints[$i+1][8][$k], 
					$volumePoints[$i+1][8][$k+1], 
					$volumePoints[$i][8][$k+1]), 
				imagecolorat($imgPng, 16+$i, 7-($k+2)));
		}
	}
	
	if($displayHairs)
	{
		//casque
		$volumePoints = array();
		for($i=0;$i<9;$i++)
		{
			for($j=0;$j<9;$j++)
			{
				if(!isset($volumePoints[$i][$j][-2]))
				{
					$volumePoints[$i][$j][-2] = new Point(array('x' => $i*9/8-0.5,'y' => $j*9/8-0.5,'z' => -2.5));
				}
				if(!isset($volumePoints[$i][$j][6]))
				{
					$volumePoints[$i][$j][6] = new Point(array('x' => $i*9/8-0.5,'y' => $j*9/8-0.5,'z' => 6.5));
				}
			}
		}
		for($j=0;$j<9;$j++)
		{
			for($k=-2;$k<7;$k++)
			{
				if(!isset($volumePoints[0][$j][$k]))
				{
					$volumePoints[0][$j][$k] = new Point(array('x' => -0.5,'y' => $j*9/8-0.5,'z' => $k*9/8-0.5));
				}if(!isset($volumePoints[8][$j][$k]))
				{
					$volumePoints[8][$j][$k] = new Point(array('x' => 8.5,'y' => $j*9/8-0.5,'z' => $k*9/8-0.5));
				}
			}
		}
		for($i=0;$i<9;$i++)
		{
			for($k=-2;$k<7;$k++)
			{
				if(!isset($volumePoints[$i][0][$k]))
				{
					$volumePoints[$i][0][$k] = new Point(array('x' => $i*9/8-0.5,'y' => -0.5,'z' => $k*9/8-0.5));
				}
				if(!isset($volumePoints[$i][8][$k]))
				{
					$volumePoints[$i][8][$k] = new Point(array('x' => $i*9/8-0.5,'y' => 8.5,'z' => $k*9/8-0.5));
				}
			}
		}
		
		for($i=0;$i<8;$i++)
		{
			for($j=0;$j<8;$j++)
			{
				$polygones['casque']['arriere'][] = new Polygon(
					array(
						$volumePoints[$i][$j][-2], 
						$volumePoints[$i+1][$j][-2], 
						$volumePoints[$i+1][$j+1][-2], 
						$volumePoints[$i][$j+1][-2]), 
					imagecolorat($imgPng, 32+31-$i, 8+$j));
				$polygones['casque']['avant'][] = new Polygon(
					array(
						$volumePoints[$i][$j][6], 
						$volumePoints[$i+1][$j][6], 
						$volumePoints[$i+1][$j+1][6], 
						$volumePoints[$i][$j+1][6]), 
					imagecolorat($imgPng, 32+8+$i, 8+$j));
			}
		}
		for($j=0;$j<8;$j++)
		{
			for($k=-2;$k<6;$k++)
			{
				$polygones['casque']['droite'][] = new Polygon(
					array(
						$volumePoints[0][$j][$k], 
						$volumePoints[0][$j][$k+1], 
						$volumePoints[0][$j+1][$k+1], 
						$volumePoints[0][$j+1][$k]), 
					imagecolorat($imgPng, 32+$k+2, 8+$j));
				$polygones['casque']['gauche'][] = new Polygon(
					array(
						$volumePoints[8][$j][$k], 
						$volumePoints[8][$j][$k+1], 
						$volumePoints[8][$j+1][$k+1], 
						$volumePoints[8][$j+1][$k]), 
					imagecolorat($imgPng, 32+23-$k-2, 8+$j));
			}
		}
		for($i=0;$i<8;$i++)
		{
			for($k=-2;$k<6;$k++)
			{
				$polygones['casque']['haut'][] = new Polygon(
					array(
						$volumePoints[$i][0][$k], 
						$volumePoints[$i+1][0][$k], 
						$volumePoints[$i+1][0][$k+1], 
						$volumePoints[$i][0][$k+1]), 
					imagecolorat($imgPng, 32+8+$i, $k+2));
				$polygones['casque']['bas'][] = new Polygon(
					array(
						$volumePoints[$i][8][$k], 
						$volumePoints[$i+1][8][$k], 
						$volumePoints[$i+1][8][$k+1], 
						$volumePoints[$i][8][$k+1]), 
					imagecolorat($imgPng, 32+16+$i, 7-($k+2)));
			}
		}
	}
	
	if(!$headOnly)
	{
		//Torse
		$volumePoints = array();
		for($i=0;$i<9;$i++)
		{
			for($j=0;$j<13;$j++)
			{
				if(!isset($volumePoints[$i][$j][0]))
				{
					$volumePoints[$i][$j][0] = new Point(array('x' => $i,'y' => $j+8,'z' => 0));
				}
				if(!isset($volumePoints[$i][$j][4]))
				{
					$volumePoints[$i][$j][4] = new Point(array('x' => $i,'y' => $j+8,'z' => 4));
				}
			}
		}
		for($j=0;$j<13;$j++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[0][$j][$k]))
				{
					$volumePoints[0][$j][$k] = new Point(array('x' => 0,'y' => $j+8,'z' => $k));
				}if(!isset($volumePoints[8][$j][$k]))
				{
					$volumePoints[8][$j][$k] = new Point(array('x' => 8,'y' => $j+8,'z' => $k));
				}
			}
		}
		for($i=0;$i<9;$i++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[$i][0][$k]))
				{
					$volumePoints[$i][0][$k] = new Point(array('x' => $i,'y' => 0+8,'z' => $k));
				}
				if(!isset($volumePoints[$i][12][$k]))
				{
					$volumePoints[$i][12][$k] = new Point(array('x' => $i,'y' => 12+8,'z' => $k));
				}
			}
		}
		
		for($i=0;$i<8;$i++)
		{
			for($j=0;$j<12;$j++)
			{
				$polygones['torse']['arriere'][] = new Polygon(
					array(
						$volumePoints[$i][$j][0], 
						$volumePoints[$i+1][$j][0], 
						$volumePoints[$i+1][$j+1][0], 
						$volumePoints[$i][$j+1][0]), 
					imagecolorat($imgPng, 39-$i, 20+$j));
				$polygones['torse']['avant'][] = new Polygon(
					array(
						$volumePoints[$i][$j][4], 
						$volumePoints[$i+1][$j][4], 
						$volumePoints[$i+1][$j+1][4], 
						$volumePoints[$i][$j+1][4]), 
					imagecolorat($imgPng, 20+$i, 20+$j));
			}
		}
		for($j=0;$j<12;$j++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['torse']['droite'][] = new Polygon(
					array(
						$volumePoints[0][$j][$k], 
						$volumePoints[0][$j][$k+1], 
						$volumePoints[0][$j+1][$k+1], 
						$volumePoints[0][$j+1][$k]), 
					imagecolorat($imgPng, 16+$k, 20+$j));
				$polygones['torse']['gauche'][] = new Polygon(
					array(
						$volumePoints[8][$j][$k], 
						$volumePoints[8][$j][$k+1], 
						$volumePoints[8][$j+1][$k+1], 
						$volumePoints[8][$j+1][$k]), 
					imagecolorat($imgPng, 31-$k, 20+$j));
			}
		}
		for($i=0;$i<8;$i++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['torse']['haut'][] = new Polygon(
					array(
						$volumePoints[$i][0][$k], 
						$volumePoints[$i+1][0][$k], 
						$volumePoints[$i+1][0][$k+1], 
						$volumePoints[$i][0][$k+1]), 
					imagecolorat($imgPng, 20+$i, 16+$k));
				$polygones['torse']['bas'][] = new Polygon(
					array(
						$volumePoints[$i][12][$k], 
						$volumePoints[$i+1][12][$k], 
						$volumePoints[$i+1][12][$k+1], 
						$volumePoints[$i][12][$k+1]), 
					imagecolorat($imgPng, 28+$i, 19-$k));
			}
		}
		
		//BrasG
		$volumePoints = array();
		for($i=0;$i<9;$i++)
		{
			for($j=0;$j<13;$j++)
			{
				if(!isset($volumePoints[$i][$j][0]))
				{
					$volumePoints[$i][$j][0] = new Point(array('x' => $i-4,'y' => $j+8,'z' => 0));
				}
				if(!isset($volumePoints[$i][$j][4]))
				{
					$volumePoints[$i][$j][4] = new Point(array('x' => $i-4,'y' => $j+8,'z' => 4));
				}
			}
		}
		for($j=0;$j<13;$j++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[0][$j][$k]))
				{
					$volumePoints[0][$j][$k] = new Point(array('x' => 0-4,'y' => $j+8,'z' => $k));
				}if(!isset($volumePoints[8][$j][$k]))
				{
					$volumePoints[4][$j][$k] = new Point(array('x' => 4-4,'y' => $j+8,'z' => $k));
				}
			}
		}
		for($i=0;$i<9;$i++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[$i][0][$k]))
				{
					$volumePoints[$i][0][$k] = new Point(array('x' => $i-4,'y' => 0+8,'z' => $k));
				}
				if(!isset($volumePoints[$i][12][$k]))
				{
					$volumePoints[$i][12][$k] = new Point(array('x' => $i-4,'y' => 12+8,'z' => $k));
				}
			}
		}
		
		for($i=0;$i<4;$i++)
		{
			for($j=0;$j<12;$j++)
			{
				$polygones['brasD']['arriere'][] = new Polygon(
					array(
						$volumePoints[$i][$j][0], 
						$volumePoints[$i+1][$j][0], 
						$volumePoints[$i+1][$j+1][0], 
						$volumePoints[$i][$j+1][0]), 
					imagecolorat($imgPng, 55-$i, 20+$j));
				$polygones['brasD']['avant'][] = new Polygon(
					array(
						$volumePoints[$i][$j][4], 
						$volumePoints[$i+1][$j][4], 
						$volumePoints[$i+1][$j+1][4], 
						$volumePoints[$i][$j+1][4]), 
					imagecolorat($imgPng, 44+$i, 20+$j));
			}
		}
		for($j=0;$j<12;$j++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['brasD']['droite'][] = new Polygon(
					array(
						$volumePoints[0][$j][$k], 
						$volumePoints[0][$j][$k+1], 
						$volumePoints[0][$j+1][$k+1], 
						$volumePoints[0][$j+1][$k]), 
					imagecolorat($imgPng, 40+$k, 20+$j));
				$polygones['brasD']['gauche'][] = new Polygon(
					array(
						$volumePoints[4][$j][$k], 
						$volumePoints[4][$j][$k+1], 
						$volumePoints[4][$j+1][$k+1], 
						$volumePoints[4][$j+1][$k]), 
					imagecolorat($imgPng, 51-$k, 20+$j));
			}
		}
		for($i=0;$i<4;$i++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['brasD']['haut'][] = new Polygon(
					array(
						$volumePoints[$i][0][$k], 
						$volumePoints[$i+1][0][$k], 
						$volumePoints[$i+1][0][$k+1], 
						$volumePoints[$i][0][$k+1]), 
					imagecolorat($imgPng, 44+$i, 16+$k));
				$polygones['brasD']['bas'][] = new Polygon(
					array(
						$volumePoints[$i][12][$k], 
						$volumePoints[$i+1][12][$k], 
						$volumePoints[$i+1][12][$k+1], 
						$volumePoints[$i][12][$k+1]), 
					imagecolorat($imgPng, 48+$i, 19-$k));
			}
		}
		
		//BrasD
		$volumePoints = array();
		for($i=0;$i<9;$i++)
		{
			for($j=0;$j<13;$j++)
			{
				if(!isset($volumePoints[$i][$j][0]))
				{
					$volumePoints[$i][$j][0] = new Point(array('x' => $i+8,'y' => $j+8,'z' => 0));
				}
				if(!isset($volumePoints[$i][$j][4]))
				{
					$volumePoints[$i][$j][4] = new Point(array('x' => $i+8,'y' => $j+8,'z' => 4));
				}
			}
		}
		for($j=0;$j<13;$j++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[0][$j][$k]))
				{
					$volumePoints[0][$j][$k] = new Point(array('x' => 0+8,'y' => $j+8,'z' => $k));
				}if(!isset($volumePoints[8][$j][$k]))
				{
					$volumePoints[4][$j][$k] = new Point(array('x' => 4+8,'y' => $j+8,'z' => $k));
				}
			}
		}
		for($i=0;$i<9;$i++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[$i][0][$k]))
				{
					$volumePoints[$i][0][$k] = new Point(array('x' => $i+8,'y' => 0+8,'z' => $k));
				}
				if(!isset($volumePoints[$i][12][$k]))
				{
					$volumePoints[$i][12][$k] = new Point(array('x' => $i+8,'y' => 12+8,'z' => $k));
				}
			}
		}
		
		for($i=0;$i<4;$i++)
		{
			for($j=0;$j<12;$j++)
			{
				$polygones['brasG']['arriere'][] = new Polygon(
					array(
						$volumePoints[$i][$j][0], 
						$volumePoints[$i+1][$j][0], 
						$volumePoints[$i+1][$j+1][0], 
						$volumePoints[$i][$j+1][0]), 
					imagecolorat($imgPng, 55-(3-$i), 20+$j));
				$polygones['brasG']['avant'][] = new Polygon(
					array(
						$volumePoints[$i][$j][4], 
						$volumePoints[$i+1][$j][4], 
						$volumePoints[$i+1][$j+1][4], 
						$volumePoints[$i][$j+1][4]), 
					imagecolorat($imgPng, 44+(3-$i), 20+$j));
			}
		}
		for($j=0;$j<12;$j++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['brasG']['droite'][] = new Polygon(
					array(
						$volumePoints[0][$j][$k], 
						$volumePoints[0][$j][$k+1], 
						$volumePoints[0][$j+1][$k+1], 
						$volumePoints[0][$j+1][$k]), 
					imagecolorat($imgPng, 40+(3-$k), 20+$j));
				$polygones['brasG']['gauche'][] = new Polygon(
					array(
						$volumePoints[4][$j][$k], 
						$volumePoints[4][$j][$k+1], 
						$volumePoints[4][$j+1][$k+1], 
						$volumePoints[4][$j+1][$k]), 
					imagecolorat($imgPng, 51-(3-$k), 20+$j));
			}
		}
		for($i=0;$i<4;$i++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['brasG']['haut'][] = new Polygon(
					array(
						$volumePoints[$i][0][$k], 
						$volumePoints[$i+1][0][$k], 
						$volumePoints[$i+1][0][$k+1], 
						$volumePoints[$i][0][$k+1]), 
					imagecolorat($imgPng, 44+(3-$i), 16+$k));
				$polygones['brasG']['bas'][] = new Polygon(
					array(
						$volumePoints[$i][12][$k], 
						$volumePoints[$i+1][12][$k], 
						$volumePoints[$i+1][12][$k+1], 
						$volumePoints[$i][12][$k+1]), 
					imagecolorat($imgPng, 48+(3-$i), 19-$k));
			}
		}
		
		//JambeG
		$volumePoints = array();
		for($i=0;$i<9;$i++)
		{
			for($j=0;$j<13;$j++)
			{
				if(!isset($volumePoints[$i][$j][0]))
				{
					$volumePoints[$i][$j][0] = new Point(array('x' => $i,'y' => $j+20,'z' => 0));
				}
				if(!isset($volumePoints[$i][$j][4]))
				{
					$volumePoints[$i][$j][4] = new Point(array('x' => $i,'y' => $j+20,'z' => 4));
				}
			}
		}
		for($j=0;$j<13;$j++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[0][$j][$k]))
				{
					$volumePoints[0][$j][$k] = new Point(array('x' => 0,'y' => $j+20,'z' => $k));
				}if(!isset($volumePoints[8][$j][$k]))
				{
					$volumePoints[4][$j][$k] = new Point(array('x' => 4,'y' => $j+20,'z' => $k));
				}
			}
		}
		for($i=0;$i<9;$i++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[$i][0][$k]))
				{
					$volumePoints[$i][0][$k] = new Point(array('x' => $i,'y' => 0+20,'z' => $k));
				}
				if(!isset($volumePoints[$i][12][$k]))
				{
					$volumePoints[$i][12][$k] = new Point(array('x' => $i,'y' => 12+20,'z' => $k));
				}
			}
		}
		
		for($i=0;$i<4;$i++)
		{
			for($j=0;$j<12;$j++)
			{
				$polygones['jambeD']['arriere'][] = new Polygon(
					array(
						$volumePoints[$i][$j][0], 
						$volumePoints[$i+1][$j][0], 
						$volumePoints[$i+1][$j+1][0], 
						$volumePoints[$i][$j+1][0]), 
					imagecolorat($imgPng, 15-$i, 20+$j));
				$polygones['jambeD']['avant'][] = new Polygon(
					array(
						$volumePoints[$i][$j][4], 
						$volumePoints[$i+1][$j][4], 
						$volumePoints[$i+1][$j+1][4], 
						$volumePoints[$i][$j+1][4]), 
					imagecolorat($imgPng, 4+$i, 20+$j));
			}
		}
		for($j=0;$j<12;$j++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['jambeD']['droite'][] = new Polygon(
					array(
						$volumePoints[0][$j][$k], 
						$volumePoints[0][$j][$k+1], 
						$volumePoints[0][$j+1][$k+1], 
						$volumePoints[0][$j+1][$k]), 
					imagecolorat($imgPng, 0+$k, 20+$j));
				$polygones['jambeD']['gauche'][] = new Polygon(
					array(
						$volumePoints[4][$j][$k], 
						$volumePoints[4][$j][$k+1], 
						$volumePoints[4][$j+1][$k+1], 
						$volumePoints[4][$j+1][$k]), 
					imagecolorat($imgPng, 11-$k, 20+$j));
			}
		}
		for($i=0;$i<4;$i++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['jambeD']['haut'][] = new Polygon(
					array(
						$volumePoints[$i][0][$k], 
						$volumePoints[$i+1][0][$k], 
						$volumePoints[$i+1][0][$k+1], 
						$volumePoints[$i][0][$k+1]), 
					imagecolorat($imgPng, 4+$i, 16+$k));
				$polygones['jambeD']['bas'][] = new Polygon(
					array(
						$volumePoints[$i][12][$k], 
						$volumePoints[$i+1][12][$k], 
						$volumePoints[$i+1][12][$k+1], 
						$volumePoints[$i][12][$k+1]), 
					imagecolorat($imgPng, 8+$i, 19-$k));
			}
		}
		
		//JambeD
		$volumePoints = array();
		for($i=0;$i<9;$i++)
		{
			for($j=0;$j<13;$j++)
			{
				if(!isset($volumePoints[$i][$j][0]))
				{
					$volumePoints[$i][$j][0] = new Point(array('x' => $i+4,'y' => $j+20,'z' => 0));
				}
				if(!isset($volumePoints[$i][$j][4]))
				{
					$volumePoints[$i][$j][4] = new Point(array('x' => $i+4,'y' => $j+20,'z' => 4));
				}
			}
		}
		for($j=0;$j<13;$j++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[0][$j][$k]))
				{
					$volumePoints[0][$j][$k] = new Point(array('x' => 0+4,'y' => $j+20,'z' => $k));
				}if(!isset($volumePoints[8][$j][$k]))
				{
					$volumePoints[4][$j][$k] = new Point(array('x' => 4+4,'y' => $j+20,'z' => $k));
				}
			}
		}
		for($i=0;$i<9;$i++)
		{
			for($k=0;$k<5;$k++)
			{
				if(!isset($volumePoints[$i][0][$k]))
				{
					$volumePoints[$i][0][$k] = new Point(array('x' => $i+4,'y' => 0+20,'z' => $k));
				}
				if(!isset($volumePoints[$i][12][$k]))
				{
					$volumePoints[$i][12][$k] = new Point(array('x' => $i+4,'y' => 12+20,'z' => $k));
				}
			}
		}
		
		for($i=0;$i<4;$i++)
		{
			for($j=0;$j<12;$j++)
			{
				$polygones['jambeG']['arriere'][] = new Polygon(
					array(
						$volumePoints[$i][$j][0], 
						$volumePoints[$i+1][$j][0], 
						$volumePoints[$i+1][$j+1][0], 
						$volumePoints[$i][$j+1][0]), 
					imagecolorat($imgPng, 15-(3-$i), 20+$j));
				$polygones['jambeG']['avant'][] = new Polygon(
					array(
						$volumePoints[$i][$j][4], 
						$volumePoints[$i+1][$j][4], 
						$volumePoints[$i+1][$j+1][4], 
						$volumePoints[$i][$j+1][4]), 
					imagecolorat($imgPng, 4+(3-$i), 20+$j));
			}
		}
		for($j=0;$j<12;$j++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['jambeG']['droite'][] = new Polygon(
					array(
						$volumePoints[0][$j][$k], 
						$volumePoints[0][$j][$k+1], 
						$volumePoints[0][$j+1][$k+1], 
						$volumePoints[0][$j+1][$k]), 
					imagecolorat($imgPng, 0+(3-$k), 20+$j));
				$polygones['jambeG']['gauche'][] = new Polygon(
					array(
						$volumePoints[4][$j][$k], 
						$volumePoints[4][$j][$k+1], 
						$volumePoints[4][$j+1][$k+1], 
						$volumePoints[4][$j+1][$k]), 
					imagecolorat($imgPng, 11-(3-$k), 20+$j));
			}
		}
		for($i=0;$i<4;$i++)
		{
			for($k=0;$k<4;$k++)
			{
				$polygones['jambeG']['haut'][] = new Polygon(
					array(
						$volumePoints[$i][0][$k], 
						$volumePoints[$i+1][0][$k], 
						$volumePoints[$i+1][0][$k+1], 
						$volumePoints[$i][0][$k+1]), 
					imagecolorat($imgPng, 4+(3-$i), 16+$k));
				$polygones['jambeG']['bas'][] = new Polygon(
					array(
						$volumePoints[$i][12][$k], 
						$volumePoints[$i+1][12][$k], 
						$volumePoints[$i+1][12][$k+1], 
						$volumePoints[$i][12][$k+1]), 
					imagecolorat($imgPng, 8+(3-$i), 19-$k));
			}
		}
		
	}
	//pré projection (rotations des membres si besoin)
	
	
	$times[] = array('Generation-polygones', microtime_float());
	
	
	foreach($polygones['tete'] as $face)
	{
		foreach($face as $poly)
		{
			$poly->preProject(4, 8, 2, $anglesMembres['tete']['ca'], $anglesMembres['tete']['sa'], $anglesMembres['tete']['cw'], $anglesMembres['tete']['sw']);
		}
	}
	if($displayHairs)
	{
		foreach($polygones['casque'] as $face)
		{
			foreach($face as $poly)
			{
				$poly->preProject(4, 8, 2, $anglesMembres['tete']['ca'], $anglesMembres['tete']['sa'], $anglesMembres['tete']['cw'], $anglesMembres['tete']['sw']);
			}
		}
	}
	if(!$headOnly)
	{
		foreach($polygones['brasD'] as $face)
		{
			foreach($face as $poly)
			{
				$poly->preProject(-2, 8, 2, $anglesMembres['brasD']['ca'], $anglesMembres['brasD']['sa'], $anglesMembres['brasD']['cw'], $anglesMembres['brasD']['sw']);
			}
		}
		foreach($polygones['brasG'] as $face)
		{
			foreach($face as $poly)
			{
				$poly->preProject(10, 8, 2, $anglesMembres['brasG']['ca'], $anglesMembres['brasG']['sa'], $anglesMembres['brasG']['cw'], $anglesMembres['brasG']['sw']);
			}
		}
		
		foreach($polygones['jambeD'] as $face)
		{
			foreach($face as $poly)
			{
				$poly->preProject(2, 20, ($anglesMembres['jambeD']['sa']<0?0:4), $anglesMembres['jambeD']['ca'], $anglesMembres['jambeD']['sa'], $anglesMembres['jambeD']['cw'], $anglesMembres['jambeD']['sw']);
			}
		}
		foreach($polygones['jambeG'] as $face)
		{
			foreach($face as $poly)
			{
				$poly->preProject(6, 20, ($anglesMembres['jambeG']['sa']<0?0:4), $anglesMembres['jambeG']['ca'], $anglesMembres['jambeG']['sa'], $anglesMembres['jambeG']['cw'], $anglesMembres['jambeG']['sw']);
			}
		}
	}
	$times[] = array('Rotation-membres', microtime_float());
	
	//On les projette tous pour avoir la taille de la fenêtre
	foreach($polygones as $morceau)
	{
		foreach($morceau as $face)
		{
			foreach($face as $poly)
			{
				if(!$poly->isProjected()) {
					$poly->project();
				}
			}
		}
	}
	
	$times[] = array('Projection-plan', microtime_float());
	
	$width = $maxX - $minX;
	$height = $maxY - $minY;
	//var_dump($minX,$maxX, $width, $minY,$maxY, $height);
	
	$ratio = intval($_GET['ratio']);
	if($ratio < 2)
		$ratio = 2;
	
	
	//cache
	//$seconds_to_cache = 60*60*24*7;//see at the begining of the file
	if($seconds_to_cache > 0) {
		$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . ' GMT';
		header('Expires: ' . $ts);
		header('Pragma: cache');
		header('Cache-Control: max-age=' . $seconds_to_cache);
	}
	
	if($_GET['format']=='svg')
	{
	
		header('Content-Type: image/svg+xml');
	 
		echo '<?xml version="1.0" standalone="no"?>
		<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
		"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
		 
		<svg width="100%" height="100%" version="1.1"
		xmlns="http://www.w3.org/2000/svg" viewBox="'.$minX.' '.$minY.' '.$width.' '.$height.'">';
	} else {
		header('Content-type: image/png');
		$image = imagecreatetruecolor($ratio * $width +1, $ratio * $height +1);
		
		imagesavealpha($image, true);

		$trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
		imagefill($image, 0, 0, $trans_colour);
	}
	
	
	$ordreAffichage = array();
	
	if(in_array('haut', $facesAvant))
	{
		if(in_array('droite', $facesAvant))
		{
			$ordreAffichage[] = array('jambeG' => $facesArriere);
			$ordreAffichage[] = array('jambeG' => $facesVisibles['jambeG']['avant']);
			$ordreAffichage[] = array('jambeD' => $facesArriere);
			$ordreAffichage[] = array('jambeD' => $facesVisibles['jambeD']['avant']);
			
			
			$ordreAffichage[] = array('brasG' => $facesArriere);
			$ordreAffichage[] = array('brasG' => $facesVisibles['brasG']['avant']);
			$ordreAffichage[] = array('torse' => $facesArriere);
			$ordreAffichage[] = array('torse' => $facesVisibles['torse']['avant']);
			$ordreAffichage[] = array('brasD' => $facesArriere);
			$ordreAffichage[] = array('brasD' => $facesVisibles['brasD']['avant']);
		} else {
			$ordreAffichage[] = array('jambeD' => $facesArriere);
			$ordreAffichage[] = array('jambeD' => $facesVisibles['jambeD']['avant']);
			$ordreAffichage[] = array('jambeG' => $facesArriere);
			$ordreAffichage[] = array('jambeG' => $facesVisibles['jambeG']['avant']);
			
			
			$ordreAffichage[] = array('brasD' => $facesArriere);
			$ordreAffichage[] = array('brasD' => $facesVisibles['brasD']['avant']);
			$ordreAffichage[] = array('torse' => $facesArriere);
			$ordreAffichage[] = array('torse' => $facesVisibles['torse']['avant']);
			$ordreAffichage[] = array('brasG' => $facesArriere);
			$ordreAffichage[] = array('brasG' => $facesVisibles['brasG']['avant']);
		}
		
		$ordreAffichage[] = array('casque' => $facesArriere);
		$ordreAffichage[] = array('tete' => $facesArriere);
		$ordreAffichage[] = array('tete' => $facesVisibles['tete']['avant']);
		$ordreAffichage[] = array('casque' => $facesVisibles['tete']['avant']);
		
	} else {
		$ordreAffichage[] = array('casque' => $facesArriere);
		$ordreAffichage[] = array('tete' => $facesArriere);
		$ordreAffichage[] = array('tete' => $facesVisibles['tete']['avant']);
		$ordreAffichage[] = array('casque' => $facesVisibles['tete']['avant']);
		
		if(in_array('droite', $facesAvant))
		{
			$ordreAffichage[] = array('brasG' => $facesArriere);
			$ordreAffichage[] = array('brasG' => $facesVisibles['brasG']['avant']);
			$ordreAffichage[] = array('torse' => $facesArriere);
			$ordreAffichage[] = array('torse' => $facesVisibles['torse']['avant']);
			$ordreAffichage[] = array('brasD' => $facesArriere);
			$ordreAffichage[] = array('brasD' => $facesVisibles['brasD']['avant']);
			
			
			$ordreAffichage[] = array('jambeG' => $facesArriere);
			$ordreAffichage[] = array('jambeG' => $facesVisibles['jambeG']['avant']);
			$ordreAffichage[] = array('jambeD' => $facesArriere);
			$ordreAffichage[] = array('jambeD' => $facesVisibles['jambeD']['avant']);
		} else {
			$ordreAffichage[] = array('brasD' => $facesArriere);
			$ordreAffichage[] = array('brasD' => $facesVisibles['brasD']['avant']);
			$ordreAffichage[] = array('torse' => $facesArriere);
			$ordreAffichage[] = array('torse' => $facesVisibles['torse']['avant']);
			$ordreAffichage[] = array('brasG' => $facesArriere);
			$ordreAffichage[] = array('brasG' => $facesVisibles['brasG']['avant']);
			
			
			$ordreAffichage[] = array('jambeD' => $facesArriere);
			$ordreAffichage[] = array('jambeD' => $facesVisibles['jambeD']['avant']);
			$ordreAffichage[] = array('jambeG' => $facesArriere);
			$ordreAffichage[] = array('jambeG' => $facesVisibles['jambeG']['avant']);
		}
		
	}
	
	
	$times[] = array('Calcul-affichage-faces', microtime_float());
	
	foreach($ordreAffichage as $morceaux)
	{
		foreach($morceaux as $morceau => $faces)
		{
			foreach($faces as $face)
			{
				foreach ($polygones[$morceau][$face] as $poly) {
					if($_GET['format']=='svg')
						echo $poly->getSvgPolygon(1);
					else
						$poly->addPngPolygon($image, $minX, $minY, $ratio);
				}
			}
		}
	}
	
	$times[] = array('Affichage-image', microtime_float());
	
	//echo '<circle cx="0" cy="0" r="1" fill="black"/>';
	if($_GET['format']=='svg')
	{
		echo '</svg>' . "\n";
		
		for($i=1;$i<count($times);$i++)
		{
			echo '<!-- ' . ($times[$i][1] - $times[$i-1][1])*1000 . 'ms : ' . $times[$i][0] . ' -->' . "\n";
		}
		echo '<!-- TOTAL : ' . ($times[count($times)-1][1] - $times[0][1])*1000 . 'ms -->' . "\n";
	}
	else
	{
		imagepng($image);
		imagedestroy($image);
		for($i=1;$i<count($times);$i++)
		{
			header('generation-time-'.$i .'-' .$times[$i][0].': ' . ($times[$i][1] - $times[$i-1][1])*1000 . 'ms');
		}
		header('generation-time-'.count($times) .'-TOTAL: ' . ($times[count($times)-1][1] - $times[0][1])*1000 . 'ms');
	}
	
	
	class Point
	{
		private $_originCoord;
		private $_destCoord = array();
		private $_isProjected = false;
		private $_isPreProjected = false;
		
		function __construct($originCoord)//constructeur parametrer
		{
			if(is_array($originCoord) && count($originCoord)==3) {
				$this->_originCoord = array(
					'x' => (isset($originCoord['x'])?$originCoord['x']:0),
					'y' => (isset($originCoord['y'])?$originCoord['y']:0),
					'z' => (isset($originCoord['z'])?$originCoord['z']:0));
			} else {
				$this->_originCoord = array('x' => 0,'y' => 0,'z' => 0);
			}
		}
		
		function project() {
			global $ca, $sa, $cw, $sw;
			global $minX, $maxX, $minY, $maxY;
			//1,0,1,0
			$x = $this->_originCoord['x'];
			$y = $this->_originCoord['y'];
			$z = $this->_originCoord['z'];
			
			/*$this->_destCoord['x'] = $x*$cw - $y*$sw;
			$this->_destCoord['y'] = $x*$ca*$sw + $y*$cw*$ca - $z*$sa;
			$this->_destCoord['z'] = $x*$sw*$sa + $y*$cw+$sa + $z*$ca;*/
			
			$this->_destCoord['x'] = $x*$cw + $z*$sw;
			$this->_destCoord['y'] = $x*$sa*$sw + $y*$ca - $z*$sa*$cw;
			$this->_destCoord['z'] = -$x*$ca*$sw + $y*$sa + $z*$ca*$cw;
			
			$this->_isProjected = true;
			
			$minX = min($minX, $this->_destCoord['x']);
			$maxX = max($maxX, $this->_destCoord['x']);
			$minY = min($minY, $this->_destCoord['y']);
			$maxY = max($maxY, $this->_destCoord['y']);
		}
		
		function preProject($dx, $dy, $dz, $ca, $sa, $cw, $sw) {
			if(!$this->_isPreProjected)
			{
				$x = $this->_originCoord['x'] - $dx;
				$y = $this->_originCoord['y'] - $dy;
				$z = $this->_originCoord['z'] - $dz;
				
				$this->_originCoord['x'] = $x*$cw + $z*$sw + $dx;
				$this->_originCoord['y'] = $x*$sa*$sw + $y*$ca - $z*$sa*$cw + $dy;
				$this->_originCoord['z'] = -$x*$ca*$sw + $y*$sa + $z*$ca*$cw + $dz;
				$this->_isPreProjected = true;
			}
		}
		
		
		
		function getOriginCoord()
		{
			return $this->_originCoord;
		}
		function getDestCoord()
		{
			return $this->_destCoord;
		}
		
		function getDepth()
		{
			if(!$this->_isProjected)
				$this->project();
			return $this->_destCoord['z'];
		}
		function isProjected()
		{
			return $this->_isProjected;
		}
	}
	
	class Polygon
	{
		private $_dots;
		private $_color;
		private $_isProjected = false;
		private $_face = 'w';
		private $_faceDepth = 0;
		function __construct($dots,$color)
		{
			
			//we suppose that $dots is an array of Point
			//fuck you if not
			$this->_dots = $dots;
			$this->_color = $color;
			$c0 = $dots[0]->getOriginCoord();
			$c1 = $dots[1]->getOriginCoord();
			$c2 = $dots[2]->getOriginCoord();
			if($c0['x'] == $c1['x'] && $c1['x'] == $c2['x']) {
				$this->_face = 'x';
				$this->_faceDepth = $c0['x'];
			} else if($c0['y'] == $c1['y'] && $c1['y'] == $c2['y']) {
				$this->_face = 'y';
				$this->_faceDepth = $c0['y'];
			} else if($c0['z'] == $c1['z'] && $c1['z'] == $c2['z']) {
				$this->_face = 'z';
				$this->_faceDepth = $c0['z'];
			}
		}
		function getFace() {
			return $this->_face;
		}
		function getFaceDepth() {
			if(!$this->_isProjected) {
				$this->project();
			}
			return $this->_faceDepth;
		}
		function getSvgPolygon($ratio) {
			$points2d = '';
			
			$r = ($this->_color >> 16) & 0xFF;
			$g = ($this->_color >> 8) & 0xFF;
			$b = $this->_color & 0xFF;
			$a     = (127-(($this->_color & 0x7F000000) >> 24))/127;
			
			if($a == 0)
				return '';
			
			foreach ($this->_dots as $dot) {
				$coord = $dot->getDestCoord();
				$points2d .= $coord['x']*$ratio . ',' .$coord['y']*$ratio . ' ';
			}
			
			
			
			$comment = '';
			/*if ($this->_color == 0xFF0000 || $this->_color == 0x0000FF)
				$comment = '<!-- ' . $this-> _face . ' ' . $this-> _faceDepth . ' ' . $this->_maxDepth . '-->';*/
			
			return $comment . '<polygon points="'.$points2d.'" style="fill:rgba('.$r.','.$g.','.$b.','.$a.')" />' . "\n";
			//return '<polygon points="'.$points2d.'" style="fill:rgba('.$r.','.$g.','.$b.','.$a.');stroke:rgba('.$r.','.$g.','.$b.','.$a.');stroke-width:0.03" />' . "\n";
		}
		
		function addPngPolygon(&$image, $minX, $minY, $ratio)
		{
			$points2d = array();
			$nbPoints = 0;
			
			$r = ($this->_color >> 16) & 0xFF;
			$g = ($this->_color >> 8) & 0xFF;
			$b = $this->_color & 0xFF;
			$a     = (127-(($this->_color & 0x7F000000) >> 24))/127;
			if($a == 0)
				return;
			
			$samePlanX = true;//will become false if all dots are in the same plan.
			$samePlanY = true;
			
			foreach ($this->_dots as $dot) {
				$coord = $dot->getDestCoord();
				if(!isset($coordX))
					$coordX = $coord['x'];
				if(!isset($coordY))
					$coordY = $coord['y'];
				
				if($coordX != $coord['x'])
					$samePlanX = false;
				if($coordY != $coord['y'])
					$samePlanY = false;
				
				$points2d[] = ($coord['x']-$minX)*$ratio ;
				$points2d[] = ($coord['y']-$minY)*$ratio;
				$nbPoints++;
			}
			
			if(!($samePlanX || $samePlanY))
			{
				$color   = imagecolorallocate($image, $r, $g, $b);
				imagefilledpolygon($image, $points2d, $nbPoints, $color);
			}
		}
		
		function isProjected()
		{
			return $this->_isProjected;
		}
		function project()
		{
			foreach ($this->_dots as &$dot) {
				if(!$dot->isProjected())
				{
					$dot->project();
				}
			}
			$this->_isProjected = true;
		}
		
		function preProject($dx, $dy, $dz, $ca, $sa, $cw, $sw)
		{
			foreach ($this->_dots as &$dot)
			{
				$dot->preProject($dx, $dy, $dz, $ca, $sa, $cw, $sw);
			}
		}
		
		
	}
	
	//echo "lalala";
?>
