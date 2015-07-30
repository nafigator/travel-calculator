<?php
/**
 * @file    index.php
 *
 * PHP version 5.4+
 *
 * @author  Yancharuk Alexander <alex at itvault dot info>
 * @date    2015-07-30 20:47
 * @copyright MIT License
 */

$data = [
	['Moscow', 'St.Pitersbourg', 3000],
	['Moscow', 'Kazan', 3000],
	['Moscow', 'Rostov', 6000],
	['St.Pitersbourg', 'Kazan', 1000],
	['St.Pitersbourg', 'Kemerovo', 3000],
	['Perm', 'Kemerovo', 2000],
	['Perm', 'Rostov', 3000],
	['Kazan', 'Kemerovo', 1000],
	['Kazan', 'Rostov', 2000],
	['Kemerovo', 'Rostov', 5000]
];

list($path, $cost) = find_path($data, 'Kazan', 'Perm');

/**
 * Поиск кратчайшего пути между двумя локациями
 *
 * @param array  $data   Массив с локациями и ценой проезда между ними [][src, dst, cost]
 * @param string $source Название исходного пункта
 * @param string $target Название конечного пункта
 *
 * @return array
 */
function find_path(array $data, $source, $target)
{
	$graph = build_graph($data);

	// массив лучших цен кратчайшего пути для каждой локации
	$best_cost = [];
	// массив предыдущих локаций для каждой локации
	$prev_loc = array();
	// очередь из необработанных локаций
	$queue = new SplPriorityQueue();

	foreach ($graph as $src => $dst) {
		$best_cost[$src] = INF;	// изначальные значения цен бесконечны
		$prev_loc[$src] = null;	// предыдущие локации неизвестны
		foreach ($dst as $name => $cost) {
			// используем цену как приоритет в очереди
			$queue->insert($name, $cost);
		}
	}

	// цена поездки в исходный пункт = 0
	$best_cost[$source] = 0;

	while (!$queue->isEmpty()) {
		// получаем минимальную цену
		$u = $queue->extract();

		if (empty($graph[$u])) continue;

		// обрабатываем доступные маршруты для локации
		foreach ($graph[$u] as $v => $cost) {
			// альтернативная цена для маршрута
			$alt = $best_cost[$u] + $cost;

			if ($alt < $best_cost[$v]) {
				// обновляем минимальную цену для локации
				$best_cost[$v] = $alt;
				// добавляем локацию в массив предыдущих локаций для вершины графа
				$prev_loc[$v] = $u;
			}
		}
	}

	// ищем кратчайший путь и складываем его в стек
	$stack = new SplStack();
	$u = $target;
	$final_cost = 0;
	// проходим в обратном порядке от пункта назначения к исходному пункту
	while (isset($prev_loc[$u]) && $prev_loc[$u]) {
		$stack->push($u);
		$final_cost += $graph[$u][$prev_loc[$u]];
		$u = $prev_loc[$u];
	}

	$stack->push($source);

	return [$stack, $final_cost];
}

/**
 * Функция для построения графа из массива с локациями и расстояний между ними
 *
 * @param array $data
 *
 * @return array
 */
function build_graph(array $data)
{
	$graph = [];

	foreach ($data as $route) {
		$graph[$route[0]] = (isset($graph[$route[0]]))
			? array_merge($graph[$route[0]], [$route[1] => $route[2]])
			: $graph[$route[0]] = [$route[1] => $route[2]];

		$graph[$route[1]] = (isset($graph[$route[1]]))
			? array_merge($graph[$route[1]], [$route[0] => $route[2]])
			: $graph[$route[1]] = [$route[0] => $route[2]];
	}

	return $graph;
}
