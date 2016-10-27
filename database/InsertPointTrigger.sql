BEGIN
	DECLARE rrId CHAR(36);
	DECLARE student CHAR(36);
	DECLARE branch CHAR(36);
	DECLARE graphRange CHAR(36) ;
	DECLARE permanent TINYINT(1);
	DECLARE lowerBound DATE;
	DECLARE upperBound DATE;
	DECLARE evDate DATE;

	DECLARE max FLOAT;

	DECLARE eRaw FLOAT;
	DECLARE eMax FLOAT;
	DECLARE pRaw FLOAT;
	DECLARE pMax FLOAT;

	DECLARE totalRaw FLOAT;
	DECLARE totalMax FLOAT;

	SET @student = NEW.student_id;
	SET @evId = NEW.evaluation_id;

	SELECT e.branch_for_group_id, e.permanent, e.date
	INTO @branch, @permanent, @evDate
	FROM evaluations e
	WHERE e.id=@evId;

	SELECT gr.id, gr.start, gr.end
	INTO @graphRange, @lowerBound, @upperBound
	FROM graph_ranges gr
	WHERE gr.start <= @evDate
				AND gr.end >= @evDate;

	SET @rrId = (SELECT rr.id
							 FROM rr rr
							 WHERE rr.student_id=@student
										 AND rr.branch_for_group_id = @branch
										 AND rr.graph_range_id = @graphRange);

	SELECT bfg.max, r.e_raw, r.e_max, r.p_raw, r.p_max
	INTO @max, @eRaw, @eMax, @pRaw, @pMax
	FROM rr r
		INNER JOIN branch_for_groups bfg ON r.branch_for_group_id=  bfg.id
	WHERE r.id = @rrId;

	IF(@permanent) THEN
		SELECT SUM(pr.score) AS score, SUM(e.max) AS max
		INTO @pRaw, @pMax
		FROM point_results pr
			INNER JOIN evaluations e ON e.id=pr.evaluation_id
		WHERE pr.student_id=@student
					AND e.branch_for_group_id=@branch
					AND e.permanent = true
					AND e.date >= @lowerBound
					AND e.date <= @upperBound;
	ELSE
		SELECT SUM(pr.score) AS score, SUM(e.max) AS max
		INTO @eRaw, @eMax
		FROM point_results pr
			INNER JOIN evaluations e ON e.id=pr.evaluation_id
		WHERE pr.student_id=@student
					AND e.branch_for_group_id=@branch
					AND e.permanent = false
					AND e.date >= @lowerBound
					AND e.date <= @upperBound;
	END IF;

	SET @cpRaw = (@pRaw / @pMax) * @max;
	SET @ceRaw = (@eRaw / @eMax) * @max;


	IF(ISNULL(@eRaw) OR ISNULL(@pRaw)) THEN
		IF(ISNULL(@eRaw)) THEN
			SET @epTotal = (@pRaw / @pMax) * 100;
		ELSE
			SET @epTotal = (@eRaw / @eMax) * 100;
		END IF;
	ELSE
		SET @eTotal = (@eRaw / @eMax) * 40;
		SET @pTotal = (@pRaw / @pMax) * 60;
		SET @epTotal = @eTotal + @pTotal;
	END IF;


	SET @total = @epTotal / 100 * @max;
	SET @totalMax = @max;


	IF(ISNULL(@rrId)) THEN
		SET @rrId = uuid();
		IF(@permanent) THEN
			INSERT INTO rr(id, branch_for_group_id, graph_range_id, student_id, p_raw, p_max, total, max)
			VALUES(@rrId, @branch, @graphRange, @student, @cpRaw, @max, @total, @max);
		ELSE
			INSERT INTO rr(id, branch_for_group_id, graph_range_id, student_id, e_raw, e_max, total, max)
			VALUES(@rrId, @branch, @graphRange, @student, @ceRaw, @max, @total, @max);
		END IF;
	ELSE
		IF(@permanent) THEN
			UPDATE rr rs
			SET rs.p_raw=@cpRaw, rs.p_max=@max, rs.total = @total, rs.max = @max
			WHERE rs.id=@rrId;
		ELSE
			UPDATE rr rs
			SET rs.e_raw=@ceRaw, rs.e_max=@max, rs.total = @total, rs.max = @max
			WHERE rs.id=@rrId;
		END IF;
	END IF;
END